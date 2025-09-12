export interface ClaimSlice{
   readonly usrId: number | null;
   readonly usrNombre: string | null;
   readonly usrTipoUsuario: string | null;
   readonly exp: number | null;
    readonly busy: boolean;
}

export const claimInitialState: ClaimSlice = {
    usrId: null,
    usrNombre: null,
    usrTipoUsuario: null,
    exp: null,
    busy: false
};