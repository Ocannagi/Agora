export interface ClaimDTO{
    usrId: number;
    usrNombre: string;
    usrTipoUsuario: string;
    exp: number;
}

export enum TipoUsuarioEnum{
    SoporteTecnico = 'ST',
    UsuarioAnticuario = 'UA',
    UsuarioTasador = 'UT',
    UsuarioGeneral = 'UG'
}

export interface TipoUsuarioDTO{
    ttuTipoUsuario: string;
    ttuDescripcion: string;
    ttuRequiereMatricula: boolean;
}

export interface CredencialesUsuarioDTO {
    usrEmail: string;
    usrPassword: string;
}

export interface RespuestaAutenticacionDTO{
    jwt: string;
}

export type KeysClaimDTO = keyof ClaimDTO;