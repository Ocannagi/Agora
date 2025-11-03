import { AntiguedadCreacionDTO, AntiguedadDTO, TipoEstado, TipoEstadoEnum } from '../modelo/AntiguedadDTO';
import { ChangeDetectionStrategy, Component, computed, DestroyRef, effect, inject, Injector, input, signal } from '@angular/core';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { Router } from '@angular/router';
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
import { switchMap } from 'rxjs';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { UploadImagenesAntiguedad } from "../../imagenes-antiguedad/upload-imagenes-antiguedad/upload-imagenes-antiguedad";
import { ImagenAntiguedadDTO, ImagenAntiguedadOrdenDTO, ImagenesAntiguedadReordenarDTO } from '../../imagenes-antiguedad/modelo/ImagenAntiguedadDTO';
import { ListaImagenesDto } from "../../imagenes-antiguedad/lista-imagenes-dto/lista-imagenes-dto";
import { MatDialog } from '@angular/material/dialog';
import { DialogImagenesAntiguedadUpload } from '../../imagenes-antiguedad/dialog-imagenes-antiguedad-upload/dialog-imagenes-antiguedad-upload';
import { MAX_IMG_ANTIGUEDAD } from '../../imagenes-antiguedad/feautures';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { Location } from '@angular/common';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';

@Component({
  selector: 'app-crear-editar-antiguedad',
  standalone: true,
  imports: [MostrarErrores, Cargando, AutocompletarPeriodos,
    AutocompletarCategorias, AutocompletarSubcategorias, MatButtonModule
    , MatFormFieldModule, ReactiveFormsModule, MatInputModule,
    UploadImagenesAntiguedad, ListaImagenesDto, MatCheckboxModule, SwalDirective],
  templateUrl: './crear-editar-antiguedad.html',
  styleUrl: './crear-editar-antiguedad.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarAntiguedad {

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
  #location = inject(Location);
  
  readonly TipoEstadoFx = TipoEstado;

  //RESOURCES

  protected antiguedadByIdResource = this.#antService.getByIdResource(this.id, this.#injector);
  protected imagenesAntiguedadByAntIdResource = this.#imgService.getByDependenciaIdResource(this.id, this.#injector);

  //SIGNALS

  readonly perId = signal<number | null>(null);
  readonly catId = signal<number | null>(null);
  readonly scatId = signal<number | null>(null);
  readonly usrId = signal<number | null>(this.#authStore.usrId());
  readonly tipoEstadoValue = signal<string>('');
  readonly imagenesFiles = signal<File[]>([]);
  readonly imagenesDTO = signal<ImagenAntiguedadDTO[]>([]);
  readonly antiguedadModelo = signal<AntiguedadDTO | null>(null);
  readonly imagenesModelo = signal<ImagenAntiguedadDTO[] | null>(null);
  private readonly _returnTo = signal<ReadonlyArray<string | number> | string | null>(null);

  readonly maxCaracteresDescripcion = signal(500);
  readonly maxCaracteresNombre = signal(50);
  readonly periodoEditDescripcion = signal<string>('');
  readonly categoriaEditDescripcion = signal<string>('');
  readonly subcategoriaEditDescripcion = signal<string>('');

  //COMPUTED
  readonly arrayNameImgExistentes = computed(() => this.imagenesDTO().length > 0 ? this.imagenesDTO().map(img => img.imaNombreArchivo) : []);
  readonly tipoEstadoKey = computed(() => {
    const key = this.TipoEstadoFx.obtenerKeyPorValor(TipoEstadoEnum, this.tipoEstadoValue() as typeof TipoEstadoEnum[keyof typeof TipoEstadoEnum]);
    return key ?? '';
  });
  readonly esEdicion = computed(() => this.id() !== null && this.id() !== undefined);
  readonly esNuevo = computed(() => !this.esEdicion());
  readonly titulo = computed(() => this.esEdicion() ? 'Editar Antigüedad' : 'Registrar nueva Antigüedad');
  readonly deshabilitarAgregarImagenes = computed(() => this.imagenesDTO().length >= this.MaxImgAntiguedad || this.esNoDisponible());


  readonly isAlaVenta = computed(() => {
    const tipoEstadoEnum = TipoEstado.convertStringToEnum(this.tipoEstadoValue());
    return tipoEstadoEnum ? TipoEstado.isAlaVenta(tipoEstadoEnum) : false;
  });

  readonly esNoDisponible = computed(() => {
    const tipoEstadoEnum = TipoEstado.convertStringToEnum(this.tipoEstadoValue());
    return tipoEstadoEnum ? TipoEstado.isNoDisponible(tipoEstadoEnum) : false;
  });

  readonly esEstadoComprado = computed(() => {
    const tipoEstadoEnum = TipoEstado.convertStringToEnum(this.tipoEstadoValue());
    return tipoEstadoEnum ? TipoEstado.isComprado(tipoEstadoEnum) : false;
  });

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
    if (this.esEdicion() && this.imagenesAntiguedadByAntIdResource?.status() === 'error') {
      const wrapped = this.imagenesAntiguedadByAntIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if (this.esEdicion() && this.#antService.patchError() !== null) {
      lista.push(this.#antService.patchError()!);
    }
    if (this.esEdicion() && this.#imgService.patchError() !== null) {
      lista.push(this.#imgService.patchError()!);
    }

    return lista;

  });

  readonly tieneErrores = computed(() => this.errores().length > 0);

  readonly isAllValid = computed(() => {
    return this.antDescripcionFormControlSignal.status() === 'VALID'
      && this.antDescripcionFormControlSignal.value() !== null && this.antDescripcionFormControlSignal.value()!.trim().length > 0
      && this.antNombreFormControlSignal.status() === 'VALID'
      && this.antNombreFormControlSignal.value() !== null && this.antNombreFormControlSignal.value()!.trim().length > 0
      && this.perId() !== null
      && this.scatId() !== null
      && this.usrId() !== null
      && (this.esEdicion() ? this.imagenesDTO().length > 0 : this.imagenesFiles().length > 0);
  });

  readonly isNotAllValid = computed(() => !this.isAllValid());

  readonly isSomethingChange = computed(() => {
    // Comparación profunda de imágenes y resto de campos
    if (this.antiguedadModelo() === null) return false;

    const ant = this.antiguedadModelo()!;
    const descCambio = ant.antDescripcion !== this.antDescripcionFormControlSignal.value()!.trim();
    const nombreCambio = ant.antNombre !== this.antNombreFormControlSignal.value()!.trim();
    const perCambio = ant.periodo.perId !== this.perId();
    const scatCambio = ant.subcategoria.scatId !== this.scatId();
    const imgsCambio = !this.arraysImagenesIguales(this.imagenesDTO(), this.imagenesModelo());
    //const imgsNewCambio = this.imagenesDTO().length !== ant.imagenes?.length;

    return descCambio || nombreCambio || perCambio || scatCambio || imgsCambio; // || imgsNewCambio;
  });

  readonly EsEdicionYNoHayCambios = computed(() => this.esEdicion() && !this.isSomethingChange());

  //FORMULARIO
  protected antDescripcion = this.#fb.control<string>('', { validators: [Validators.required, Validators.minLength(1), Validators.maxLength(this.maxCaracteresDescripcion())], updateOn: 'change' })
  readonly antDescripcionFormControlSignal = formControlSignal(this.antDescripcion, this.#injector);

  protected antNombre = this.#fb.control<string>('', { validators: [Validators.required, Validators.minLength(1), Validators.maxLength(this.maxCaracteresNombre())], updateOn: 'change' })
  readonly antNombreFormControlSignal = formControlSignal(this.antNombre, this.#injector);

  //

  constructor() {
    const st = this.#location.getState() as { returnTo?: unknown } | null;
    const rt = Array.isArray(st?.returnTo) || typeof st?.returnTo === 'string'
      ? (st!.returnTo as ReadonlyArray<string | number> | string)
      : null;
    this._returnTo.set(rt);
    let flag = true;

    const ant = this.antiguedadByIdResource;
    const img = this.imagenesAntiguedadByAntIdResource;

    effect(() => {
      const img = this.imagenesFiles();
      console.log('Imágenes seleccionadas:', img);
    });

    effect(() => {
      if (this.isReadyToEdit() && flag) {
        console.log('Is ready to edit - mapeando datos de antigüedad e imágenes');
        flag = false;
        
        this.antiguedadModelo.set(ant.value());
        this.imagenesModelo.set(img.value());
        this.mapearAntiguedadData(ant.value());
        this.imagenesDTO.set(img.value());
      }
    });

    effect(() => {
      const imgValue = img.value();
      if(flag) return;

      this.imagenesDTO.set(imgValue);
    });

    effect(() => {
      console.log('Tipo Estado cambiado a:', this.tipoEstadoValue());
      this.ifDeshabilitarCamposEnEdicion();
    });

    this.#destroyRef.onDestroy(() => {
      this.#antService.postError.set(null);
      this.#antService.patchError.set(null);
      this.#imgService.postError.set(null);
      this.#imgService.patchError.set(null);
    });
  }

  private mapearAntiguedadData(antiguedad: AntiguedadDTO) {
    this.tipoEstadoValue.set(antiguedad.tipoEstado);
    this.antNombre.setValue(antiguedad.antNombre);
    this.antDescripcion.setValue(antiguedad.antDescripcion);
    this.periodoEditDescripcion.set(antiguedad.periodo.perDescripcion);
    this.categoriaEditDescripcion.set(antiguedad.subcategoria.categoria.catDescripcion);
    this.subcategoriaEditDescripcion.set(antiguedad.subcategoria.scatDescripcion);
  }

  private ifDeshabilitarCamposEnEdicion(): void {
    if (this.esEdicion() && this.isReadyToEdit() && this.esNoDisponible()) {
      this.antDescripcionFormControlSignal.disabled.set(true);
      this.antNombreFormControlSignal.disabled.set(true);
    }
  }

  // MÉTODOS Y EVENTOS

  obtenerErrorAntDescripcion(): string | null {
    return this.#vcf.obtenerErrorControl(this.antDescripcion, 'descripción de la antigüedad');
  }

  obtenerErrorAntNombre(): string | null {
    return this.#vcf.obtenerErrorControl(this.antNombre, 'nombre de la antigüedad');
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
            this.resetearEditDescripcion();
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

  private resetearEditDescripcion (): void {
    this.periodoEditDescripcion.set('');
    this.categoriaEditDescripcion.set('');
    this.subcategoriaEditDescripcion.set('');
  }

  protected onSubmit() {
    if (this.isNotAllValid()) {
      this.antDescripcion.markAsTouched();
      return;
    }

    if (this.esEdicion()) {
      const antiguedadEditada: AntiguedadCreacionDTO = {
        perId: this.perId()!,
        scatId: this.scatId()!,
        antNombre: this.antNombreFormControlSignal.value()!.trim(),
        antDescripcion: this.antDescripcionFormControlSignal.value()!.trim(),
        usrId: this.usrId()!,
      };

      this.#antService.update(this.id()!, antiguedadEditada).pipe(
        takeUntilDestroyed(this.#destroyRef),
        switchMap((_) => {
          const imgDTO = this.imagenesDTO();
          console.log('Imágenes actuales para reordenar:', imgDTO);
          const reordenarDTO = this.mapearAImagenesAntiguedadReordenarDTO(this.id()!, imgDTO);
          return this.#imgService.update(reordenarDTO);
        })
      ).subscribe({
        next: () => {
          this.#router.navigate(['/antiguedades']);
        },
        error: (err) => {
          // Los errores ya se manejan en los signals de los servicios
          console.error('Error al actualizar la antigüedad:', err);
        }
      });

    } else {
      //crear
      const nuevaAntiguedad: AntiguedadCreacionDTO = {
        perId: this.perId()!,
        scatId: this.scatId()!,
        antNombre: this.antNombreFormControlSignal.value()!.trim(),
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

  private mapearAImagenesAntiguedadReordenarDTO(antId: number, imagenes: ImagenAntiguedadDTO[]): ImagenesAntiguedadReordenarDTO {
    return {
      antId,
      imagenesAntiguedadOrden: imagenes.map(img => ({
        imaId: img.imaId,
        imaOrden: img.imaOrden
      })) as ImagenAntiguedadOrdenDTO[]
    };
  }

  // Comparación profunda de arrays de imágenes por identidad y orden
  private arraysImagenesIguales(
    a: ImagenAntiguedadDTO[] | null,
    b: ImagenAntiguedadDTO[] | null
  ): boolean {
    // Si alguno es null/undefined, considerar diferentes
    if (!a || !b) return false;
    if (a.length !== b.length) return false;

    // Importante: comparamos por posición para detectar reordenamientos
    for (let i = 0; i < a.length; i++) {
      const ai = a[i];
      const bi = b[i];
      if (ai.imaId !== bi.imaId) return false;
      if (ai.imaOrden !== bi.imaOrden) return false;
      // Si también querés validar metadatos visibles:
      if (ai.imaNombreArchivo !== bi.imaNombreArchivo) return false;
      if (ai.imaUrl !== bi.imaUrl) return false;
    }
    return true;
  }

  onCancel(): void {
    const rt = this._returnTo();

    if (Array.isArray(rt)) {
      const cmds = rt.filter((s): s is string | number =>
        (typeof s === 'string' && s.trim().length > 0) || typeof s === 'number'
      );
      if (cmds.length) {
        this.#router.navigate(cmds);
        return;
      }
    } else if (typeof rt === 'string' && rt.trim().length > 0) {
      this.#router.navigateByUrl(rt);
      return;
    }

    this.#router.navigate(['/antiguedades']);
  }

  protected convertToRD(){
    if(!this.esEstadoComprado())
      return;
    const antiguedadEditada: AntiguedadCreacionDTO = {
      perId: this.perId()!,
      scatId: this.scatId()!,
      antNombre: this.antNombreFormControlSignal.value()!.trim(),
      antDescripcion: this.antDescripcionFormControlSignal.value()!.trim(),
      usrId: this.usrId()!,
      tipoEstado: TipoEstadoEnum.RetiradoDisponible

    }
    this.#antService.update(this.id()!, antiguedadEditada).pipe(takeUntilDestroyed(this.#destroyRef)).subscribe({
      next: () => {
        window.location.reload();
      },
      error: (err) => {
        console.error('Error al actualizar la antigüedad:', err);
      }
    });
  }
}