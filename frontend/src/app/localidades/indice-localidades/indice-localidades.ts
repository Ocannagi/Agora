import { Component, ChangeDetectionStrategy, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { IndiceEntidad } from "../../compartidos/componentes/indice-entidad/indice-entidad";
import { LocalidadesService } from '../localidades-service';

@Component({
  selector: 'app-indice-localidades',
  templateUrl: './indice-localidades.html',
  styleUrls: ['./indice-localidades.scss'],
  imports: [MatButtonModule, MostrarErrores, IndiceEntidad],
  providers: [
    { provide: SERVICIO_PAGINADO_TOKEN, useClass: LocalidadesService },
    IndiceEntidadStore
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceLocalidades {
  protected store = inject(IndiceEntidadStore);

  constructor() {
    this.store.setTitulo('Localidades');
    this.store.setPathCrear('/localidades/crear');
  }
}