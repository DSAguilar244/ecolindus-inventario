<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente. 
            Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
        </p>
    </header>

    <button type="button" class="btn btn-outline-danger mt-3"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <i class="bi bi-trash me-1"></i>
        Eliminar cuenta
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true"
        x-show="$errors->userDeletion->isNotEmpty()">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">Confirmar eliminación de cuenta</h5>
                    <button type="button" class="btn-close" x-on:click="$dispatch('close')"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p class="mb-3">
                            ¿Estás seguro de que deseas eliminar tu cuenta? Esta acción es irreversible.
                        </p>
                        <p class="text-muted small mb-3">
                            Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente.
                            Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                required
                            >
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Eliminar cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
