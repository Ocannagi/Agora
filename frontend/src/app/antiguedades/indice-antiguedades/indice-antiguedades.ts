import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { AntiguedadesService } from '../antiguedades-service';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { IndiceEntidad } from "../../compartidos/componentes/indice-entidad/indice-entidad";

@Component({
  selector: 'app-indice-antiguedades',
  imports: [MostrarErrores, IndiceEntidad],
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
  constructor() {
    this.store.setTitulo('Antigüedades');
    this.store.setPathCrear('/antiguedades/crear');
    this.store.setColumnasExtras(['tipoEstado']);
  }

}
