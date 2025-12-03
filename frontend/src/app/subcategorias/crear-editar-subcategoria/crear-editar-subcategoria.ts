import { Component, ChangeDetectionStrategy, inject, OnInit, signal, computed } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { SubcategoriasService} from '../subcategorias-service';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { AutocompletarCategorias } from '../../categorias/autocompletar-categorias/autocompletar-categorias';
import { SubcategoriaCreacionDTO } from '../modelo/subcategoriaDTO';
@Component({
  selector: 'app-crear-editar-subcategoria',
  templateUrl: './crear-editar-subcategoria.html',
  styleUrls: ['./crear-editar-subcategoria.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MostrarErrores,
    Cargando,
    AutocompletarCategorias
  ]
})
export class CrearEditarSubcategoria implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  private readonly subcategoriasService = inject(SubcategoriasService);

  protected readonly formSubcategoria = this.fb.group({
    scatDescripcion: ['', [Validators.required, Validators.maxLength(50)]],
    catId: [null as number | null, [Validators.required]]
  });

  readonly isLoading = signal(false);
  readonly erroresSignal = signal<string[] | null>(null);
  private readonly idSignal = signal<number | null>(null);

  readonly categoriaKeyword = signal<string>('');
  readonly esEdicion = computed(() => this.idSignal() !== null);
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Subcategoría' : 'Crear Subcategoría');

readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.subcategoriasService.postError() !== null)
      lista.push(this.subcategoriasService.postError()!);
    return lista;
  });

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    this.idSignal.set(idParam ? Number(idParam) : null);

    if (this.esEdicion()) {
      this.cargarSubcategoria(this.idSignal() as number);
    }
  }

  private cargarSubcategoria(id: number): void {
    this.isLoading.set(true);
    this.subcategoriasService.getById(id).subscribe({
      next: (dto) => {
        this.formSubcategoria.patchValue({
          scatDescripcion: dto.scatDescripcion,
          catId: dto.scatId
        });
        // Establecer el keyword para mostrar la categoría actual en el autocomplete
        if (dto.categoria?.catDescripcion) {
          this.categoriaKeyword.set(dto.categoria.catDescripcion);
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
    if (this.formSubcategoria.invalid) return;

    this.isLoading.set(true);
    this.erroresSignal.set(null);
    const subcategoriaCreacionDTO = this.formSubcategoria.value as SubcategoriaCreacionDTO;

    const obs = this.esEdicion()
      ? this.subcategoriasService.update(this.idSignal() as number, subcategoriaCreacionDTO)
      : this.subcategoriasService.create(subcategoriaCreacionDTO);

    const obs$ = obs as unknown as import('rxjs').Observable<unknown>;

    obs$.subscribe({
      next: () => {
        this.isLoading.set(false);
        this.router.navigate(['/subcategorias']);
      },
      error: (err) => {
        this.isLoading.set(false);
        this.erroresSignal.set([String(err?.message ?? err)]);
      }
    });
  }

  obtenerErrorCampo(nombre: string): string | null {
    const ctrl = this.formSubcategoria.get(nombre);
    if (!ctrl || !ctrl.errors) return null;
    if (ctrl.errors['required']) return 'Este campo es obligatorio';
    if (ctrl.errors['maxlength']) return `Máximo ${ctrl.errors['maxlength'].requiredLength} caracteres`;
    return 'Valor inválido';
  }

  onCategoriaSeleccionada(id: number | null): void {
    this.formSubcategoria.patchValue({ catId: id });
  }

  cancelar(): void {
    this.router.navigate(['/subcategorias']);
  }
}