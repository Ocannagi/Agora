import { Component, ChangeDetectionStrategy, inject, OnInit, signal, computed } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { CategoriasService } from '../categorias-service';
// componentes compartidos usados en la plantilla (ajusta rutas si difieren)
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { CategoriaCreacionDTO } from '../modelo/CategoriaDTO';

@Component({
  selector: 'app-crear-editar-categoria',
  templateUrl: './crear-editar-categoria.html',
  styleUrls: ['./crear-editar-categoria.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  // si el proyecto usa standalone components: exponer los imports necesarios aquí
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MostrarErrores,
    Cargando
  ]
})
export class CrearEditarCategoria implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private route = inject(ActivatedRoute);
  private categoriasService = inject(CategoriasService);

  protected formCategoria = this.fb.group({
      catDescripcion: ['', [Validators.required, Validators.maxLength(50)]],
  });



  isLoading = signal(false);
  readonly erroresSignal = signal<string[] | null>(null);
  private idSignal = signal<number | null>(null);

  readonly esEdicion = computed(() => this.idSignal() !== null);
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Categoría' : 'Crear Categoría');

readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.categoriasService.postError() !== null)
      lista.push(this.categoriasService.postError()!);
    return lista;
  });


  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    this.idSignal.set(idParam ? Number(idParam) : null);

    if (this.esEdicion()) {
      this.cargarCategoria(this.idSignal() as number);
    }
  }


  private cargarCategoria(id: number): void {
    this.isLoading.set(true);
    this.categoriasService.getById(id).subscribe({
      next: (dto) => {
        // CORRECCIÓN: patchear los campos que existen en el form (nombre, descripcion, padreId)
        this.formCategoria.patchValue({
          catDescripcion: dto.catDescripcion,
        });
        this.isLoading.set(false);
      },
      error: (err) => {
        this.erroresSignal.set([String(err?.message ?? err)]);
        this.isLoading.set(false);
      }
    });
  }

  onSubmit(): void {
    if (this.formCategoria.invalid) return;

    this.isLoading.set(true);
    this.erroresSignal.set(null);

    const categoriaCreacionDTO = this.formCategoria.value as CategoriaCreacionDTO;

    const obs = this.esEdicion()
      ? this.categoriasService.update(this.idSignal() as number, categoriaCreacionDTO)
      : this.categoriasService.create(categoriaCreacionDTO);

    // SEGURIDAD DE TIPOS: si el servicio no está correctamente tipado, forzamos Observable antes de subscribe.
    const obs$ = obs as unknown as import('rxjs').Observable<unknown>;

    obs$.subscribe({
      next: () => {
        this.isLoading.set(false);
        this.router.navigate(['/categorias']);
      },
      error: (err) => {
        this.isLoading.set(false);
        this.erroresSignal.set([String(err?.message ?? err)]);
      }
    });
  }

  obtenerErrorCampo(nombre: string): string | null {
    const ctrl = this.formCategoria.get(nombre);
    if (!ctrl || !ctrl.errors) return null;
    if (ctrl.errors['required']) return 'Este campo es obligatorio';
    if (ctrl.errors['maxlength']) return `Máximo ${ctrl.errors['maxlength'].requiredLength} caracteres`;
    return 'Valor inválido';
  }

  cancelar(): void {
    this.router.navigate(['/categorias']);
  }


}