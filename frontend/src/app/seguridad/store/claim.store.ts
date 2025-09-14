import { patchState, signalStore, withComputed, withMethods, withState } from "@ngrx/signals";
import { claimInitialState } from "./claim.slice";
import { computed } from "@angular/core";
import { ClaimDTO, TipoUsuarioEnum } from "../seguridadDTO";


export const ClaimStore = signalStore(
    {providedIn: 'root'},
    withState(claimInitialState),
    withComputed(
        (store) => {
            const isLoggedIn = computed(() => store.usrId() !== null && store.exp() !== null && store.exp()! * 1000 > new Date().getTime());
            const isSoporteTecnico = computed(() => isLoggedIn() && store.usrTipoUsuario() === TipoUsuarioEnum.SoporteTecnico);
            const isUsrAnticuario = computed(() => isLoggedIn() && store.usrTipoUsuario() === TipoUsuarioEnum.UsuarioAnticuario);
            const isUsrTasador = computed(() => isLoggedIn() && store.usrTipoUsuario() === TipoUsuarioEnum.UsuarioTasador);
            const isUsrGeneral = computed(() => isLoggedIn() && store.usrTipoUsuario() === TipoUsuarioEnum.UsuarioGeneral);
            const isCompradorVendedor = computed(() => isSoporteTecnico() || isUsrAnticuario() || isUsrGeneral());
            const isPuedeTasar = computed(() => isUsrTasador() || isUsrAnticuario() || isSoporteTecnico());
            const isPuedeSolicitarTasacion = computed(() => isUsrGeneral() || isSoporteTecnico());
            const isBusy = computed(() => store.busy());
            const isIdle = computed(() => !isBusy());

            return {
                isLoggedIn,
                isSoporteTecnico,
                isUsrAnticuario,
                isUsrTasador,
                isUsrGeneral,
                isCompradorVendedor,
                isPuedeTasar,
                isPuedeSolicitarTasacion,
                isBusy,
                isIdle
            }
        }
    ),
    withMethods(
        (store) => {
            const setUsrId = (usrId: number | null) => patchState(store, {usrId});
            const setUsrNombre = (usrNombre: string | null) => patchState(store, {usrNombre});
            const setUsrTipoUsuario = (usrTipoUsuario: string | null) => patchState(store, {usrTipoUsuario});
            const setExp = (exp: number | null) => patchState(store, {exp});
            const setBusy = (busy: boolean) => patchState(store, {busy});

            const resetState = () => patchState(store, claimInitialState);
            const setState = (state: ClaimDTO) => patchState(store, state);

            return {
                setUsrId,
                setUsrNombre,
                setUsrTipoUsuario,
                setExp,
                setBusy,
                resetState,
                setState
            }
        }
    )
);