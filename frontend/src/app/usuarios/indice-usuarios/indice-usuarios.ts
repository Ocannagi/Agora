import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { UsuariosService } from '../usuarios-service';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { IndiceEntidad } from "../../compartidos/componentes/indice-entidad/indice-entidad";
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";

@Component({
  selector: 'app-indice-usuarios',
  imports: [IndiceEntidad, MostrarErrores],
  templateUrl: './indice-usuarios.html',
  styleUrl: './indice-usuarios.scss',
  providers: [
    {
      provide: SERVICIO_PAGINADO_TOKEN, useClass: UsuariosService
    },
    IndiceEntidadStore
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceUsuarios {
  protected store = inject(IndiceEntidadStore);
  constructor() {
    this.store.setTitulo('Usuarios');
    this.store.setPathCrear('/usuarios/crear');
    this.store.setColumnasExtras(['usrEmail', 'usrTipoUsuario']);
    this.store.setFiltrarPorUsrId(false); // Los usuarios no se filtran por el usrId del usuario autenticado
  }
}
