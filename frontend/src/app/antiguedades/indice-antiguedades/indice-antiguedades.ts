import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { AntiguedadesService } from '../antiguedades-service';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { MatButtonModule } from '@angular/material/button';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatTableModule } from '@angular/material/table';
import { RouterLink } from '@angular/router';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';
import { Cargando } from '../../compartidos/componentes/cargando/cargando';
import { ListadoGenerico } from '../../compartidos/componentes/listado-generico/listado-generico';
import { TituloExtraSeparador } from '../../compartidos/modelo/IIndiceEntidadDTO';
//import { JsonPipe } from '@angular/common';
import { MatTooltip } from '@angular/material/tooltip';
import { TipoEstadoPipe } from '../../compartidos/pipes/tipo-estado-pipe';

@Component({
  selector: 'app-indice-antiguedades',
  imports: [MostrarErrores, RouterLink, MatButtonModule, ListadoGenerico, MatTableModule, MatPaginatorModule,
    SwalDirective, MostrarErrores, Cargando, MatTooltip, TipoEstadoPipe],
  templateUrl: './indice-antiguedades.html',
  styleUrl: './indice-antiguedades.scss',
  providers: [
      {
        provide: SERVICIO_PAGINADO_TOKEN, useClass: AntiguedadesService
      },
      IndiceEntidadStore
    ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceAntiguedades {
  protected store = inject(IndiceEntidadStore);
  protected TituloExtraSeparador = TituloExtraSeparador

  constructor() {
    this.store.setTitulo('Antigüedades');
    this.store.setPathCrear('/antiguedades/crear');
    this.store.setColumnasExtras(['tipoEstado']);
    this.store.setFiltrarPorUsrId(true); // por default es true, pero prefiero explicitarlo
    this.store.setMsgBorrar('La antigüedad será retirada del Ágora');
  }

}
