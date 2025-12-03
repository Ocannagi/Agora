import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, Injector, input } from '@angular/core';
import { numberAttributeOrNull } from '../../../compartidos/funciones/transform';
import { AntiguedadesRetiradasNdService } from '../antiguedades-retiradas-nd-service';
import { ImagenesAntiguedadService } from '../../../imagenes-antiguedad/imagenes-antiguedad-service';
import { MostrarErrores } from "../../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { Cargando } from "../../../compartidos/componentes/cargando/cargando";
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatButtonModule } from '@angular/material/button';
import { ListaImagenesDto } from "../../../imagenes-antiguedad/lista-imagenes-dto/lista-imagenes-dto";
import { Location } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { AntiguedadCreacionDTO, AntiguedadDTO, TipoEstado } from '../../modelo/AntiguedadDTO';
import { ImagenAntiguedadDTO } from '../../../imagenes-antiguedad/modelo/ImagenAntiguedadDTO';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Router } from '@angular/router';

@Component({
  selector: 'app-ver-antiguedad-retirada-nd',
  imports: [
    MostrarErrores,
    Cargando,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    ListaImagenesDto,
    SwalDirective
  ],
  templateUrl: './ver-antiguedad-retirada-nd.html',
  styleUrl: './ver-antiguedad-retirada-nd.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class VerAntiguedadRetiradaNd {
  // INPUTS
  readonly id = input(null, { transform: numberAttributeOrNull });

  // SERVICES & INYECCIONES
  readonly #injector = inject(Injector);
  readonly #antRN_Service = inject(AntiguedadesRetiradasNdService);
  readonly #imgService = inject(ImagenesAntiguedadService);
  readonly #location = inject(Location);
  readonly #destroyRef = inject(DestroyRef);
  readonly #router = inject(Router);

  readonly TipoEstadoFx = TipoEstado;

  // RESOURCES
  protected readonly antiguedadByIdResource = this.#antRN_Service.getByIdResource(this.id, this.#injector);
  protected readonly imagenesAntiguedadByAntIdResource = this.#imgService.getByDependenciaIdResource(this.id, this.#injector);

  // COMPUTED
  readonly antiguedad = computed<AntiguedadDTO | null>(() => {
    if (this.antiguedadByIdResource.status() === 'resolved') {
      return this.antiguedadByIdResource.value();
    }
    return null;
  });

  readonly imagenes = computed<ImagenAntiguedadDTO[]>(() => {
    if (this.imagenesAntiguedadByAntIdResource.status() === 'resolved') {
      return this.imagenesAntiguedadByAntIdResource.value();
    }
    return [];
  });

  readonly titulo = computed(() => {
    const ant = this.antiguedad();
    return ant ? `Ver Antigüedad - ${ant.antNombre}` : 'Ver Antigüedad';
  });

  readonly tipoEstadoKey = computed(() => {
    const ant = this.antiguedad();
    if (!ant) return '';
    const key = this.TipoEstadoFx.obtenerKeyPorValor(
      TipoEstado as any,
      ant.tipoEstado as any
    );
    return key ?? '';
  });

  readonly esCargando = computed(() => {
    const antiguedadRes = this.antiguedadByIdResource;
    const imagenesRes = this.imagenesAntiguedadByAntIdResource;
    return antiguedadRes?.isLoading() || imagenesRes?.isLoading();
  });

  readonly errores = computed(() => {
    const lista: string[] = [];

    if (this.antiguedadByIdResource?.status() === 'error') {
      const wrapped = this.antiguedadByIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }

    if (this.imagenesAntiguedadByAntIdResource?.status() === 'error') {
      const wrapped = this.imagenesAntiguedadByAntIdResource?.error();
      const httpError = wrapped?.cause as HttpErrorResponse;
      lista.push(httpError?.error as string ?? httpError?.message ?? wrapped?.message ?? 'Error desconocido');
    }

    return lista;
  });

  readonly periodoDescripcion = computed(() => this.antiguedad()?.periodo.perDescripcion ?? '');
  readonly categoriaDescripcion = computed(() => this.antiguedad()?.subcategoria.categoria.catDescripcion ?? '');
  readonly subcategoriaDescripcion = computed(() => this.antiguedad()?.subcategoria.scatDescripcion ?? '');
  readonly antNombre = computed(() => this.antiguedad()?.antNombre ?? '');
  readonly antDescripcion = computed(() => this.antiguedad()?.antDescripcion ?? '');

  // MÉTODOS
  protected onVolver(): void {
    this.#location.back();
  }

  protected convertToRD(): void {
    const antiguedad: AntiguedadCreacionDTO = {
      perId: this.antiguedad()!.periodo.perId,
      scatId: this.antiguedad()!.subcategoria.scatId,
      antNombre: this.antiguedad()!.antNombre,
      antDescripcion: this.antiguedad()!.antDescripcion,
      usrId: this.antiguedad()!.usuario.usrId,
      tipoEstado: TipoEstado.RetiradoDisponible(),
    } as AntiguedadCreacionDTO;

    this.#antRN_Service.update(this.id()!, antiguedad).pipe(takeUntilDestroyed(this.#destroyRef)).subscribe({
      next: () => {
        this.#router.navigate(['/antiguedades']);
      },
      error: (err) => {
        console.error('Error al actualizar la antigüedad:', err);
      }
    });
  }
}