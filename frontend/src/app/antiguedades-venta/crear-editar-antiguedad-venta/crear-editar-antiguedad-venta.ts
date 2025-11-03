import { ChangeDetectionStrategy, Component, computed, DestroyRef, effect, inject, Injector, input, signal } from '@angular/core';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { Form, FormBuilder, FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { ValidaControlForm } from '../../compartidos/servicios/valida-control-form';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { AntiguedadesVentaService } from '../antiguedades-venta-service';
import { AntiguedadesService } from '../../antiguedades/antiguedades-service';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';
import { AntiguedadALaVentaCreacionDTO, AntiguedadALaVentaDTO } from '../modelo/AntiguedadAlaVentaDTO';
import { AntiguedadDTO } from '../../antiguedades/modelo/AntiguedadDTO';
import { DomicilioDTO } from '../../domicilios/modelo/domicilioDTO';
import { HttpErrorResponse } from '@angular/common/http';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { NgOptimizedImage } from '@angular/common';
import { UsuariosDomiciliosService } from '../../usuarios-domicilios/usuario-domicilios-service';
import { UsuarioDTO } from '../../usuarios/modelo/usuarioDTO';
import { TasacionDigitalDTO } from '../../tasaciones-digitales/modelo/tasacionDigitalDTO';
import { formControlSignal } from '../../compartidos/funciones/formToSignal';
import { MatDialog } from '@angular/material/dialog';
import { DialogAgregarDomicilio } from '../../usuarios-domicilios/dialog-agregar-domicilio/dialog-agregar-domicilio';


@Component({
  selector: 'app-crear-editar-antiguedad-venta',
  imports: [
    MostrarErrores, Cargando,
    ReactiveFormsModule, RouterLink,
    MatButtonModule, MatFormFieldModule, MatInputModule, MatSelectModule,
    NgOptimizedImage
  ],
  templateUrl: './crear-editar-antiguedad-venta.html',
  styleUrl: './crear-editar-antiguedad-venta.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CrearEditarAntiguedadVenta {

  // Input de ruta: id de Antigüedad a la venta (para edición)
  readonly id = input(null, { transform: numberAttributeOrNull });

  // Inyecciones de servicios
  #fb = inject(FormBuilder);
  #vcf = inject(ValidaControlForm);
  #router = inject(Router);
  #destroyRef = inject(DestroyRef);
  #injector = inject(Injector);
  #auth = inject(AutenticacionStore);
  #aavService = inject(AntiguedadesVentaService);
  #antService = inject(AntiguedadesService);
  #usrDomService = inject(UsuariosDomiciliosService);
  #dialog = inject(MatDialog); // Inyectar el servicio de diálogo (Modal)

  // Estado
  readonly usrId = signal<number | null>(this.#auth.usrId());
  readonly antiguedadSeleccionadaId = signal<number | null>(null);
  readonly selectedDomicilioId = signal<number | null>(null);

  // Resources
  protected aavByIdResource = this.#aavService.getByIdResource(this.id, this.#injector);  // DTO completo en edición
  protected antiguedadesResource = this.#antService.getByUsrIdHabilitadoVtaResource(this.usrId, this.#injector); // Antigüedades del usuario
  protected usuariosDomiciliosResource = this.#usrDomService.getByUsrIdResource(this.usrId, this.#injector); // Domicilios del usuario

  // Formulario: solo datos de venta
  protected formVenta = this.#fb.group({
    aavPrecioVenta: this.#fb.control<number>(0, { validators: [Validators.required, Validators.min(0.01)], updateOn: 'change' }),
    tadId: this.#fb.control<number | null>({ value: null, disabled: true }, { validators: [], updateOn: 'change' }), // opcional
  });
  readonly controlAavPrecioVentaSignal = formControlSignal(this.formVenta.get('aavPrecioVenta') as FormControl, this.#injector);

  // Computeds
  readonly esEdicion = computed(() => this.id() !== null && this.id() !== undefined);
  readonly noEsEdicion = computed(() => !this.esEdicion());
  readonly titulo = computed(() => this.esEdicion() ? 'Editar publicación de Antigüedad' : 'Publicar Antigüedad a la venta');

  // Miniatura seleccionada para mostrar grande (creación y edición)
  readonly selectedThumbUrl = computed(() => {
    if (this.esEdicion() && this.aavByIdResource?.status() === 'resolved') {
      const aav = this.aavByIdResource.value() as AntiguedadALaVentaDTO;
      return this.thumbUrl(aav.antiguedad) ?? null;
    }
    const id = this.antiguedadSeleccionadaId();
    if (!id) return null;
    const lista = this.antiguedadesResource?.value() ?? [];
    const ant = lista.find(a => a.antId === id);
    return ant ? this.thumbUrl(ant) : null;
  });

  readonly esCargando = computed(() => {
    const loadAav = this.esEdicion() ? this.aavByIdResource?.isLoading() : false;
    return Boolean(loadAav || this.antiguedadesResource?.isLoading() || this.usuariosDomiciliosResource?.isLoading());
  });

  readonly nombreAntiguedadSeleccionada = computed(() => {
    if (this.esEdicion() && this.aavByIdResource?.status() === 'resolved') {
      const aav = this.aavByIdResource.value();
      return aav ? aav.antiguedad.antNombre : '';
    }
    return '';
  });

  readonly fechaPublicacionAntiguedadSeleccionada = computed(() => {
    const aav = this.aavByIdResource?.value();
    return aav ? aav.aavFechaPublicacion : '';
  });

  readonly errores = computed(() => {
    const lista: string[] = [];
    if (this.#aavService.postError()) lista.push(this.#aavService.postError()!);
    if (this.#aavService.patchError()) lista.push(this.#aavService.patchError()!);
    if (this.esEdicion() && this.aavByIdResource?.status() === 'error') {
      const wrapped = this.aavByIdResource.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if (!this.esEdicion() && this.antiguedadesResource?.status() === 'error') {
      const wrapped = this.antiguedadesResource.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    if (this.usuariosDomiciliosResource?.status() === 'error') {
      const wrapped = this.usuariosDomiciliosResource.error?.();
      const httpError = (wrapped?.cause as HttpErrorResponse);
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }
    return lista;
  });

  readonly isFormValid = computed(() => {
    const tieneAnt = this.esEdicion() ? true : this.antiguedadSeleccionadaId() !== null;
    return !!tieneAnt
      && this.usrId() !== null
      && this.selectedDomicilioId() !== null
      && this.controlAavPrecioVentaSignal.status() === 'VALID';
  });
  readonly isNotFormValid = computed(() => !this.isFormValid());

  constructor() {
    // En edición: mapear DTO a formulario
    effect(() => {
      if (this.esEdicion() && this.aavByIdResource?.status() === 'resolved') {
        const dto = this.aavByIdResource.value() as AntiguedadALaVentaDTO;

        // Setear selección de antigüedad y domicilio (solo lectura en edición)
        this.antiguedadSeleccionadaId.set(dto.antiguedad.antId);
        this.selectedDomicilioId.set(dto.domicilioOrigen.domId);

        // Precio y tasación (tadId puede ser null/undefined)
        this.formVenta.patchValue({
          aavPrecioVenta: dto.aavPrecioVenta ?? null,
          tadId: dto.tasacion?.tadId ?? null
        });
      }
    });

    // Limpiar errores al destruir
    this.#destroyRef.onDestroy(() => {
      this.#aavService.postError.set(null);
      this.#aavService.patchError.set(null);
    });
  }

  // Navegación para ver/editar la Antigüedad en su componente propio
  protected verEditarAntiguedad() {
    const antId = this.antiguedadSeleccionadaId();
    if (!antId) return;
    this.#router.navigate(['/antiguedades/editar', antId], {
      state: {
        from: 'aav-edit',
        returnTo: ['/antiguedadesAlaVenta/editar', this.id()] // volver a la AAV que se estaba editando
      }
    });
  }

  private construirDTO(): AntiguedadALaVentaCreacionDTO {
    const antId = this.antiguedadSeleccionadaId()!;
    const precio = this.formVenta.get('aavPrecioVenta')?.value!;
    const tadId = this.formVenta.get('tadId')?.value!;
    const domId = this.selectedDomicilioId()!;

    return {
      antiguedad: { antId } as AntiguedadDTO,
      vendedor: { usrId: this.usrId()! } as UsuarioDTO,
      domicilioOrigen: { domId } as DomicilioDTO,
      aavPrecioVenta: Number(precio),
      tasacion: tadId ? { tadId } as TasacionDigitalDTO : undefined
    };
  }

  protected onSubmit() {
    if (this.isNotFormValid()) {
      this.formVenta.markAllAsTouched();
      return;
    }

    const dto = this.construirDTO();

    if (this.esEdicion()) {
      this.#aavService.update(this.id()!, dto)
        .pipe(takeUntilDestroyed(this.#destroyRef))
        .subscribe({
          next: () => this.#router.navigate(['/antiguedadesAlaVenta']),
          error: () => { }
        });
    } else {

      this.#aavService.create(dto)
        .pipe(takeUntilDestroyed(this.#destroyRef))
        .subscribe({
          next: () => this.#router.navigate(['/antiguedadesAlaVenta']),
          error: () => { }
        });
    }
  }

  // Devuelve la URL de la miniatura (primera imagen por orden) o null si no hay imágenes
  protected thumbUrl(ant: AntiguedadDTO): string | null {
    const arr = ant.imagenes ?? [];
    if (!arr || arr.length === 0) return null;
    const portada = [...arr].sort((a, b) => (a.imaOrden ?? 0) - (b.imaOrden ?? 0))[0];
    return portada.imaUrl;
  }

  // Texto legible del domicilio para el select
  protected formatDomicilio(dom: DomicilioDTO): string {
    const calle = dom.domCalleRuta ?? '';
    const nro = dom.domNroKm ?? '';
    // Tolerar estructura anidada o plana
    const loc = (dom as any).localidad?.locDescripcion ?? (dom as any).locDescripcion ?? '';
    const prov = (dom as any).localidad?.provincia?.provDescripcion ?? (dom as any).provDescripcion ?? '';
    const cpa = dom.domCPA ?? '';
    return [`${calle} ${nro}`.trim(), [loc, prov].filter(Boolean).join(' - '), cpa].filter(Boolean).join(' | ');
  }

  obtenerErrorCampoVenta(arg0: string) {
    return this.#vcf.obtenerErrorCampoGroup(this.formVenta.controls, arg0,true);
  }

  protected openDialog(): void {
    const dialogRef = this.#dialog.open(DialogAgregarDomicilio, {
      disableClose: true,
    });

    const subscription = dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      if (result === true) {
        this.usuariosDomiciliosResource.reload();
      }
    });

    this.#destroyRef.onDestroy(() => {
      subscription.unsubscribe();
    });
  }

}
