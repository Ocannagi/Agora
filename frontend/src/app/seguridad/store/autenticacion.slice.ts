export interface AutenticacionSlice {
    readonly jwt: string | null;
    readonly busy: boolean;
    readonly errors: string[];
}

export const autenticacionInitialState: AutenticacionSlice = {
    jwt: null,
    busy: false,
    errors: []
};

export type PersistedAutenticacionSlice = Pick<AutenticacionSlice, 'jwt'>;