import { AntiguedadCreacionDTO, AntiguedadDTO } from './../modelo/AntiguedadDTO';
import { ChangeDetectionStrategy, Component, computed, DestroyRef, effect, inject, Injector, input, signal, untracked } from '@angular/core';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { Router, RouterLink } from '@angular/router';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { Cargando } from "../../compartidos/componentes/cargando/cargando";
import { formControlSignal } from '../../compartidos/funciones/formToSignal';
import { AntiguedadesService } from '../antiguedades-service';
import { ImagenesAntiguedadService } from '../../imagenes-antiguedad/imagenes-antiguedad-service';
import { HttpErrorResponse } from '@angular/common/http';
import { AutocompletarPeriodos } from "../../periodos/autocompletar-periodos/autocompletar-periodos";
import { AutocompletarCategorias } from "../../categorias/autocompletar-categorias/autocompletar-categorias";
import { AutocompletarSubcategorias } from "../../subcategorias/autocompletar-subcategorias/autocompletar-subcategorias";
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatButtonModule } from '@angular/material/button';
import { switchMap, take } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { UploadImagenesAntiguedad } from "../../imagenes-antiguedad/upload-imagenes-antiguedad/upload-imagenes-antiguedad";
import { ImagenAntiguedadDTO } from '../../imagenes-antiguedad/modelo/ImagenAntiguedadDTO';
import { ListaImagenesDto } from "../../imagenes-antiguedad/lista-imagenes-dto/lista-imagenes-dto";
import { MatDialog } from '@angular/material/dialog';
import { Dialog } from '@angular/cdk/dialog';
import { DialogImagenesAntiguedadUpload } from '../../imagenes-antiguedad/dialog-imagenes-antiguedad-upload/dialog-imagenes-antiguedad-upload';
import { MAX_IMG_ANTIGUEDAD } from '../../imagenes-antiguedad/feautures';

@Component({
  selector: 'app-crear-editar-antiguedad',
  standalone: true,
  imports: [MostrarErrores, Cargando, AutocompletarPeriodos, AutocompletarCategorias, AutocompletarSubcategorias, MatButtonModule, RouterLink, MatFormFieldModule, ReactiveFormsModule, MatInputModule, UploadImagenesAntiguedad, ListaImagenesDto],
  templateUrl: './crear-editar-antiguedad.component.html',
  styleUrl: './crear-editar-antiguedad.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarAntiguedadComponent {

  //INPUTS
  readonly id = input(null, { transform: numberAttributeOrNull });
  private MaxImgAntiguedad = MAX_IMG_ANTIGUEDAD;

  //SERVICES & INYECCIONES
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #destroyRef = inject(DestroyRef);
  #injector = inject(Injector);
  #router = inject(Router);
  #authStore = inject(AutenticacionStore);
  #antService = inject(AntiguedadesService);
  #imgService = inject(ImagenesAntiguedadService);
  #dialog = inject(MatDialog); // Inyectar el servicio de diálogo (Modal)


  //FORMULARIO
  protected antDescripcion = this.#fb.control<string>('', { validators: [Validators.required, Validators.minLength(1), Validators.maxLength(500)], updateOn: 'change' })
  readonly antDescripcionFormControlSignal = formControlSignal(this.antDescripcion, this.#injector);

  //RESOURCES

  protected antiguedadByIdResource = this.#antService.getByIdResource(this.id, this.#injector);
  protected imagenesAntiguedadByAntIdResource = this.#imgService.getByDependenciaIdResource(this.id, this.#injector);

  //SIGNALS

  readonly perId = signal<number | null>(null);
  readonly catId = signal<number | null>(null);
  readonly scatId = signal<number | null>(null);
  readonly usrId = signal<number | null>(this.#authStore.usrId());
  readonly imagenesFiles = signal<File[]>([]);
  readonly imagenesDTO = signal<ImagenAntiguedadDTO[]>([]);

  readonly maxCaracteresDescripcion = signal(500);
  readonly periodoEditDescripcion = signal<string>('');
  readonly categoriaEditDescripcion = signal<string>('');
  readonly subcategoriaEditDescripcion = signal<string>('');

  //COMPUTED
  readonly arrayNameImgExistentes = computed(() => this.imagenesDTO().length > 0 ? this.imagenesDTO().map(img => img.imaNombreArchivo) : []);
  readonly esEdicion = computed(() => this.id() !== null && this.id() !== undefined);
  readonly esNuevo = computed(() => !this.esEdicion());
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Antigüedad' : 'Registrar nueva Antigüedad');
  readonly deshabilitarAgregarImagenes = computed(() => this.imagenesDTO().length >= this.MaxImgAntiguedad);

  readonly esCargando = computed(() => {
    if (this.esEdicion()) {
      const antiguedadRes = this.antiguedadByIdResource;
      const imagenesRes = this.imagenesAntiguedadByAntIdResource;
      if (imagenesRes?.isLoading())
        return true;  
      if (antiguedadRes?.isLoading())
        return true;
    }

    return false;
  });

  readonly isReadyToEdit = computed(() => {
    const esEdicion = this.esEdicion();
    const antRes = this.antiguedadByIdResource;
    const imgRes = this.imagenesAntiguedadByAntIdResource;
    
    return esEdicion && antRes.status() === 'resolved' && imgRes.status() === 'resolved';
  });

  readonly errores = computed(() => {
    const lista: string[] = [];

    if (this.#antService.postError() !== null)
      lista.push(this.#antService.postError()!);
    if (this.#imgService.postError() !== null)
      lista.push(this.#imgService.postError()!);
    if (this.esEdicion() && this.antiguedadByIdResource?.status() === 'error') {
      const wrapped = this.antiguedadByIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if( this.esEdicion() && this.imagenesAntiguedadByAntIdResource?.status() === 'error') {
      const wrapped = this.imagenesAntiguedadByAntIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if (this.esEdicion() && this.#antService.patchError() !== null) {
      lista.push(this.#antService.patchError()!);
    }

    return lista;

  });

  readonly tieneErrores = computed(() => this.errores().length > 0);

  readonly isAllValid = computed(() => {
    return this.antDescripcionFormControlSignal.status() === 'VALID'
      && this.antDescripcionFormControlSignal.value() !== null && this.antDescripcionFormControlSignal.value()!.trim().length > 0
      && this.perId() !== null
      && this.scatId() !== null
      && this.usrId() !== null
      && this.imagenesFiles().length > 0;
  });

  readonly isNotAllValid = computed(() => !this.isAllValid());


  constructor() {

    effect(() => {
      const img = this.imagenesFiles();
      console.log('Imágenes seleccionadas:', img);
    });

    effect(() => {
      if (this.isReadyToEdit()) {
        const ant = this.antiguedadByIdResource;
        const img = this.imagenesAntiguedadByAntIdResource;
        untracked(() => {
          this.mapearAntiguedadData(ant.value())
          this.imagenesDTO.set(img.value());
        });
      }
    });



    this.#destroyRef.onDestroy(() => {
      this.#antService.postError.set(null);
      this.#antService.patchError.set(null);
      this.#imgService.postError.set(null);
    });


  }

  mapearAntiguedadData(antiguedad: AntiguedadDTO) {
    console.log('Mapeando datos de la antigüedad para edición:', antiguedad);
    this.antDescripcionFormControlSignal.value.set(antiguedad.antDescripcion);
    this.periodoEditDescripcion.set(antiguedad.periodo.perDescripcion);
    this.categoriaEditDescripcion.set(antiguedad.subcategoria.categoria.catDescripcion);
    this.subcategoriaEditDescripcion.set(antiguedad.subcategoria.scatDescripcion);
  }

  // MÉTODOS Y EVENTOS

  obtenerErrorAntDescripcion(): string | null {
    return this.#vcf.obtenerErrorControl(this.antDescripcion, 'descripción de la antigüedad');
  }

  protected openDialog(): void {
    const dialogRef = this.#dialog.open(DialogImagenesAntiguedadUpload, {
      disableClose: true,
      data: { arrayNameImgExistentes: this.arrayNameImgExistentes() },
    });

    const subscription = dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      if (result !== undefined) {
        this.#imgService.create(result, this.id()!).pipe(
          takeUntilDestroyed(this.#destroyRef)
        ).subscribe({
          next: () => {
            this.imagenesAntiguedadByAntIdResource.reload();
          },
          error: (err) => {
            console.error('Error al subir imágenes:', err);
          }
        });
      }
    });

    this.#destroyRef.onDestroy(() => {
      subscription.unsubscribe();
    });
  }

  protected onSubmit() {
    if (this.isNotAllValid()) {
      this.antDescripcion.markAsTouched();
      return;
    }

    if (this.esEdicion()) {
      //editar
    } else {
      //crear
      const nuevaAntiguedad: AntiguedadCreacionDTO = {
        perId: this.perId()!,
        scatId: this.scatId()!,
        antDescripcion: this.antDescripcionFormControlSignal.value()!.trim(),
        usrId: this.usrId()!,
      }

      this.#antService.create(nuevaAntiguedad).pipe(
        switchMap((antId: number) => {
          const archivos = this.imagenesFiles();
          return this.#imgService.create(archivos, antId);
        }),
        takeUntilDestroyed(this.#destroyRef)
      ).subscribe({
        next: (imgIds: number[]) => {
          this.#router.navigate(['/antiguedades']);
        },
        error: (err) => {
          // Los errores ya se manejan en los signals de los servicios
          console.error('Error al crear la antigüedad o subir las imágenes:', err);
        }
      });

    }
  }


}