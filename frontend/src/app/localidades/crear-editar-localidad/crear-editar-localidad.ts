import { Component, ChangeDetectionStrategy, inject, OnInit, signal, computed } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { LocalidadesService } from '../localidades-service';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { AutocompletarProvincias } from '../../provincias/autocompletar-provincias/autocompletar-provincias';
import { LocalidadCreacionDTO } from '../modelo/localidadDTO';
@Component({
  selector: 'app-crear-editar-localidad',
  templateUrl: './crear-editar-localidad.html',
  styleUrls: ['./crear-editar-localidad.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MostrarErrores,
    Cargando,
    AutocompletarProvincias
  ]
})
export class CrearEditarLocalidad implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  private readonly localidadesService = inject(LocalidadesService);

  protected readonly formLocalidad = this.fb.group({
    locDescripcion: ['', [Validators.required, Validators.maxLength(50)]],
    provId: [null as number | null, [Validators.required]]
  });

  readonly isLoading = signal(false);
  readonly erroresSignal = signal<string[] | null>(null);
  private readonly idSignal = signal<number | null>(null);

  readonly provinciaKeyword = signal<string>('');

  readonly esEdicion = computed(() => this.idSignal() !== null);
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Localidad' : 'Crear Localidad');

readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.localidadesService.postError() !== null)
      lista.push(this.localidadesService.postError()!);
    return lista;
  });

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    this.idSignal.set(idParam ? Number(idParam) : null);

    if (this.esEdicion()) {
      this.cargarLocalidad(this.idSignal() as number);
    }
  }

  private cargarLocalidad(id: number): void {
    this.isLoading.set(true);
    this.localidadesService.getById(id).subscribe({
      next: (dto) => {
        this.formLocalidad.patchValue({
          locDescripcion: dto.locDescripcion,
          provId: dto.locId 
        });
        if (dto.provincia?.provDescripcion) {
          this.provinciaKeyword.set(dto.provincia.provDescripcion);
        }
        this.isLoading.set(false);
      },
      error: (err) => {
        this.erroresSignal.set([String(err?.message ?? err)]);
        this.isLoading.set(false);
      }
    });
  }

  onSubmit(): void {
    if (this.formLocalidad.invalid) return;

    this.isLoading.set(true);
    this.erroresSignal.set(null);

    const localidadCreacionDTO = this.formLocalidad.value as LocalidadCreacionDTO;


    const obs = this.esEdicion()
      ? this.localidadesService.update(this.idSignal() as number, localidadCreacionDTO)
      : this.localidadesService.create(localidadCreacionDTO);
    
      const obs$ = obs as unknown as import('rxjs').Observable<unknown>;

    obs$.subscribe({
      next: () => {
        this.isLoading.set(false);
        this.router.navigate(['/localidades']);
      },
      error: (err) => {
        this.isLoading.set(false);
        this.erroresSignal.set([String(err?.message ?? err)]);
      }
    });
  }

  obtenerErrorCampo(nombre: string): string | null {
    const ctrl = this.formLocalidad.get(nombre);
    if (!ctrl || !ctrl.errors) return null;
    if (ctrl.errors['required']) return 'Este campo es obligatorio';
    if (ctrl.errors['maxlength']) return `Máximo ${ctrl.errors['maxlength'].requiredLength} caracteres`;
    return 'Valor inválido';
  }

  onProvinciaSeleccionada(id: number | null): void {
    this.formLocalidad.patchValue({ provId: id });
  }

  cancelar(): void {
    this.router.navigate(['/localidades']);
  }
}