import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from '../../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { TituloExtraSeparador } from '../../../compartidos/modelo/IIndiceEntidadDTO';
import { SERVICIO_PAGINADO_TOKEN } from '../../../compartidos/proveedores/tokens';
import { MostrarErrores } from "../../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { IndiceEntidad } from "../../../compartidos/componentes/indice-entidad/indice-entidad";
import { AntiguedadesRetiradasNdService } from '../antiguedades-retiradas-nd-service';

@Component({
  selector: 'app-indice-antiguedades-retiradas-nd',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-antiguedades-retiradas-nd.html',
  styleUrl: './indice-antiguedades-retiradas-nd.scss',
  providers: [
        {
          provide: SERVICIO_PAGINADO_TOKEN, useClass: AntiguedadesRetiradasNdService
        },
        IndiceEntidadStore
      ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceAntiguedadesRetiradasNd {
  protected store = inject(IndiceEntidadStore);
  protected TituloExtraSeparador = TituloExtraSeparador

  constructor() {
    this.store.setTitulo('Antigüedades Retiradas - No Disponibles');
    this.store.setFiltrarPorUsrId(true); // por default es true, pero prefiero explicitarlo
    this.store.setColumnasExtras(['antFechaEstado']);
  }

}
