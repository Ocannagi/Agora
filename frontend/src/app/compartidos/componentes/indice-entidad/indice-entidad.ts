import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from './store-indice-entidad/indice-entidad.store';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatTableModule } from '@angular/material/table';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';
import { ListadoGenerico } from '../listado-generico/listado-generico';
import { MostrarErrores } from '../mostrar-errores/mostrar-errores';
import { Cargando } from "../cargando/cargando";
import { TituloExtraSeparador } from '../../modelo/IIndiceEntidadDTO';

@Component({
  selector: 'app-indice-entidad',
  imports: [RouterLink, MatButtonModule, ListadoGenerico, MatTableModule, MatPaginatorModule, SwalDirective, MostrarErrores, Cargando],
  templateUrl: './indice-entidad.html',
  styleUrl: './indice-entidad.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceEntidad {
  protected store = inject(IndiceEntidadStore);
  protected TituloExtraSeparador = TituloExtraSeparador

  // Obtiene el “título” de la columna extra desde el primer elemento del listado
  extraTitulo = (columna: string): string => {
    const arr = this.store.resourcePaginado.value()?.arrayEntidad ?? [];
    const raw = (arr[0] as Record<string, any> | undefined)?.[columna];
    if (typeof raw !== 'string') return ''; 
    return raw.split(TituloExtraSeparador)[0] ?? '';
  };

  // Obtiene el “valor” (segunda parte) para cada fila
  valorExtra = (raw: unknown): string => {
    if (typeof raw !== 'string') return '';
    const parts = raw.split(TituloExtraSeparador);
    return parts[1] ?? '';
  };
}
