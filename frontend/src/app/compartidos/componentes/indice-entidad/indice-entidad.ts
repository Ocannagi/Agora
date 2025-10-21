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

@Component({
  selector: 'app-indice-entidad',
  imports: [RouterLink, MatButtonModule, ListadoGenerico, MatTableModule, MatPaginatorModule, SwalDirective, MostrarErrores, Cargando],
  templateUrl: './indice-entidad.html',
  styleUrl: './indice-entidad.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceEntidad {

  protected store = inject(IndiceEntidadStore);



}
