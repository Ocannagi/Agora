import { DomicilioDTO } from "../../domicilios/modelo/domicilioDTO";
import { UsuarioDTO } from "../../usuarios/modelo/usuarioDTO";

export interface UsuarioDomiciliosDTO {
    usuario : UsuarioDTO
    domicilios : DomicilioDTO[]
}

export interface UsuarioDomiciliosCreacionDTO {
    udomUsr: number
    udomDom: number
}