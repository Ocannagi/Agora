import { deepComputed, patchState, signalStore, withComputed, withHooks, withMethods, withProps, withState } from "@ngrx/signals";
import { computed, effect, inject, Injector, Signal } from "@angular/core";
import { ClaimDTO, CredencialesUsuarioDTO, KeysClaimDTO, TipoUsuarioEnum } from "../seguridadDTO";
import { SeguridadService } from "../seguridad-service";
import { autenticacionInitialState, PersistedAutenticacionSlice } from "./autenticacion.slice";
import { rxMethod } from "@ngrx/signals/rxjs-interop";
import { pipe, switchMap, tap } from "rxjs";
import { tapResponse } from "@ngrx/operators";
import { HttpErrorResponse } from "@angular/common/http";
import { Router } from "@angular/router";


export const AutenticacionStore = signalStore(
    {providedIn: 'root'},
    withState(autenticacionInitialState),
    withProps((_) => {
        const _seguridadService = inject(SeguridadService);
        const _injector = inject(Injector);
        const router = inject(Router);

        
        return {
            _seguridadService,
            _injector,
            router
        }

    }),
    withMethods(
        (store) => {
            const _setJwt = (jwt: string) => patchState(store, {jwt});
            const setBusy = (busy: boolean) => patchState(store, {busy});
            const setErrors = (errors: string[]) => patchState(store, {errors});
            const setOneError = (error: string) => patchState(store, {errors : [...store.errors(), error]});

            const resetState = () => patchState(store, autenticacionInitialState);

            const login = rxMethod<CredencialesUsuarioDTO>(pipe(
                    tap(() => {
                        setBusy(true);
                        setErrors([]);
                    }),
                    switchMap(credenciales => {return store._seguridadService.login(credenciales).pipe(
                        tapResponse({
                            next: (response) => {
                                response.body ? _setJwt(response.body.jwt) : '';
                                store.router.navigate(['/']);
                            },
                            error: (error: HttpErrorResponse) => {
                                setOneError(error.message);
                                console.error(error);
                            },
                            finalize: () => setBusy(false)
                        })
                    )})

                ), { injector: store._injector });


            return {
                _setJwt,
                setBusy,
                resetState,
                setErrors,
                setOneError,
                login
            }
        }
    ),
    withComputed(
        (store) => {
            const getFieldFromJWT = (field : KeysClaimDTO) : string | number | null => {
                if(store.jwt() === null)
                    return null;

                let dataToken
                
                try {
                    dataToken = JSON.parse(atob(store.jwt()!.split('.')[1]))  
                } catch (error) {
                    if (error instanceof SyntaxError) {
                        store.setOneError("Error de sintaxis en el token JWT");
                    }

                    if (error instanceof Error)
                        store.setOneError(error.message);
                }

                if (!dataToken || !(field in dataToken)) {
                    store.setOneError("Error al obtener los datos del token JWT");
                    return null;
                }

                return dataToken[field] ?? null;
            }

            const usrId = computed(() => getFieldFromJWT('usrId') as number | null);
            const usrNombre = computed(() => getFieldFromJWT('usrNombre') as string | null);
            const usrTipoUsuario = computed(() => getFieldFromJWT('usrTipoUsuario') as string | null);
            const exp = computed(() => getFieldFromJWT('exp') as number | null);

            const ClaimDTOSignal = deepComputed<ClaimDTO>(() => ({
                usrId: usrId() ?? 0,
                usrNombre: usrNombre() ?? '',
                usrTipoUsuario: usrTipoUsuario() ?? '',
                exp: exp() ?? 0,
            }));

            const isLoggedIn = computed(() => usrId() !== null && exp() !== null && exp()! * 1000 > new Date().getTime());
            const isSoporteTecnico = computed(() => isLoggedIn() && usrTipoUsuario() === TipoUsuarioEnum.SoporteTecnico);
            const isUsrAnticuario = computed(() => isLoggedIn() && usrTipoUsuario() === TipoUsuarioEnum.UsuarioAnticuario);
            const isUsrTasador = computed(() => isLoggedIn() && usrTipoUsuario() === TipoUsuarioEnum.UsuarioTasador);
            const isUsrGeneral = computed(() => isLoggedIn() && usrTipoUsuario() === TipoUsuarioEnum.UsuarioGeneral);
            const isCompradorVendedor = computed(() => isSoporteTecnico() || isUsrAnticuario() || isUsrGeneral());
            const isPuedeTasar = computed(() => isUsrTasador() || isUsrAnticuario() || isSoporteTecnico());
            const isPuedeSolicitarTasacion = computed(() => isUsrGeneral() || isSoporteTecnico());
            const isBusy = computed(() => store.busy());
            const isIdle = computed(() => !isBusy());
            const isError = computed(() => store.errors().length > 0);

            return {
                usrId,
                usrNombre,
                usrTipoUsuario,
                exp,
                ClaimDTOSignal,
                isLoggedIn,
                isSoporteTecnico,
                isUsrAnticuario,
                isUsrTasador,
                isUsrGeneral,
                isCompradorVendedor,
                isPuedeTasar,
                isPuedeSolicitarTasacion,
                isBusy,
                isIdle,
                isError
            }
        }
    ),
    withHooks(store => ({
        onInit(){
            const persisted : Signal<PersistedAutenticacionSlice> = computed(() => ({ jwt: store.jwt() }));
            const saved = localStorage.getItem(store._seguridadService.keyToken);

            if (saved) {
                const parsed : PersistedAutenticacionSlice = { jwt: saved };
                if (persisted().jwt !== parsed.jwt) {
                    patchState(store, parsed);
                }
            }

            effect(() => {
                const persistedValue = persisted();
                localStorage.setItem(store._seguridadService.keyToken, persistedValue.jwt ?? '');
                console.log(store.ClaimDTOSignal(), store.ClaimDTOSignal().usrNombre)
            });
        }
    }))
    
);