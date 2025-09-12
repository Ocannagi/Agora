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