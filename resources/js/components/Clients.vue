<style scoped>
    .action-link {
        cursor: pointer;
    }
</style>

<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        OAuth Clients
                    </span>

                    <a class="action-link" tabindex="-1" @click="showCreateClientForm">
                        Create New Client
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Current Clients -->
                <p class="mb-0" v-if="clients.length === 0">
                    You have not created any OAuth clients.
                </p>

                <table class="table table-borderless mb-0" v-if="clients.length > 0">
                    <thead>
                    <tr>
                        <th>Client ID</th>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Secret</th>
                        <th>Trusted</th>
                        <th>SSO</th>
                        <th>SLO</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="client in clients">
                        <!-- ID -->
                        <td style="vertical-align: middle;">
                            {{ client.id }}
                        </td>

                        <!-- Name -->
                        <td style="vertical-align: middle;">
                            {{ client.name }}
                        </td>

                        <!-- Key -->
                        <td style="vertical-align: middle;">
                            <code>{{ client.key ? client.key : "-" }}</code>
                        </td>

                        <!-- Secret -->
                        <td style="vertical-align: middle;">
                            <code>{{ client.secret }}</code>
                        </td>

                        <!-- Trusted -->
                        <td style="vertical-align: middle;">
                            <code>{{ client.trusted ? "Yes" : "No" }}</code>
                        </td>

                        <!-- SSO -->
                        <td style="vertical-align: middle;">
                            <code>{{ client.sso ? "Yes" : "No" }}</code>
                        </td>

                        <!-- SLO -->
                        <td style="vertical-align: middle;">
                            <code>{{ client.slo ? "Yes" : "No" }}</code>
                        </td>

                        <!-- Edit Button -->
                        <td style="vertical-align: middle;">
                            <a class="action-link" tabindex="-1" @click="edit(client)">
                                Edit
                            </a>
                        </td>

                        <!-- Delete Button -->
                        <td style="vertical-align: middle;">
                            <a class="action-link text-danger" @click="destroy(client)">
                                Delete
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Client Modal -->
        <div class="modal fade" id="modal-create-client" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Create Client
                        </h4>

                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="createForm.errors.length > 0">
                            <p class="mb-0"><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in createForm.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Create Client Form -->
                        <form role="form">
                            <!-- Name -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Name</label>

                                <div class="col-md-9">
                                    <input id="create-client-name" type="text" class="form-control"
                                           @keyup.enter="store" v-model="createForm.name">

                                    <span class="form-text text-muted">
                                        Something your users will recognize and trust.
                                    </span>
                                </div>
                            </div>

                            <!-- Redirect URL -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Redirect URL</label>

                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="redirect"
                                           @keyup.enter="store" v-model="createForm.redirect">

                                    <span class="form-text text-muted">
                                        Your application's authorization callback URL.
                                    </span>
                                </div>
                            </div>

                            <!-- Confidential -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Confidential</label>

                                <div class="col-md-9">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" v-model="createForm.confidential">
                                        </label>
                                    </div>

                                    <span class="form-text text-muted">
                                        Require the client to authenticate with a secret. Confidential clients can hold credentials in a secure way without exposing them to unauthorized parties. Public applications, such as native desktop or JavaScript SPA applications, are unable to hold secrets securely.
                                    </span>
                                </div>
                            </div>

                            <!-- Trusted -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Trusted</label>

                                <div class="col-md-9">
                                    <input type="checkbox" name="trusted"
                                           @keyup.enter="store" v-model="createForm.trusted">

                                    <span class="form-text text-muted">
                                        Authorization confirmation page will be passed if the client is trusted.
                                    </span>
                                </div>
                            </div>

                            <!-- SSO -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">SSO</label>

                                <div class="col-md-9">
                                    <input type="checkbox" name="sso"
                                           @keyup.enter="store" v-model="createForm.sso">

                                    <span class="form-text text-muted">
                                        User will be not asked to enter the username and password if have logged in before.
                                    </span>
                                </div>
                            </div>

                            <!-- SLO URL -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">SLO URL</label>

                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="slo"
                                           @keyup.enter="store" v-model="createForm.slo">

                                    <span class="form-text text-muted">
                                        Provide client's logout URL to automatically logout user from the client.
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="store">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Client Modal -->
        <div class="modal fade" id="modal-edit-client" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Edit Client
                        </h4>

                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Form Errors -->
                        <div class="alert alert-danger" v-if="editForm.errors.length > 0">
                            <p class="mb-0"><strong>Whoops!</strong> Something went wrong!</p>
                            <br>
                            <ul>
                                <li v-for="error in editForm.errors">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>

                        <!-- Edit Client Form -->
                        <form role="form">
                            <!-- Name -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Name</label>

                                <div class="col-md-9">
                                    <input id="edit-client-name" type="text" class="form-control"
                                           @keyup.enter="update" v-model="editForm.name">

                                    <span class="form-text text-muted">
                                        Something your users will recognize and trust.
                                    </span>
                                </div>
                            </div>

                            <!-- Redirect URL -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Redirect URL</label>

                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="redirect"
                                           @keyup.enter="update" v-model="editForm.redirect">

                                    <span class="form-text text-muted">
                                        Your application's authorization callback URL.
                                    </span>
                                </div>
                            </div>

                            <!-- Trusted -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">Trusted</label>

                                <div class="col-md-9">
                                    <input type="checkbox" name="trusted"
                                           @keyup.enter="update" v-model="editForm.trusted">

                                    <span class="form-text text-muted">
                                        Authorization confirmation page will be passed if the client is trusted.
                                    </span>
                                </div>
                            </div>

                            <!-- SSO -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">SSO</label>

                                <div class="col-md-9">
                                    <input type="checkbox" name="sso"
                                           @keyup.enter="update" v-model="editForm.sso">

                                    <span class="form-text text-muted">
                                        User will be not asked to enter the username and password if have logged in before.
                                    </span>
                                </div>
                            </div>

                            <!-- SLO URL -->
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">SLO URL</label>

                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="slo"
                                           @keyup.enter="store" v-model="editForm.slo">

                                    <span class="form-text text-muted">
                                        Provide client's logout URL to automatically logout user from the client.
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                        <button type="button" class="btn btn-primary" @click="update">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                clients: [],

                createForm: {
                    errors: [],
                    name: '',
                    redirect: '',
                    confidential: true,
                    trusted: false,
                    sso: true,
                    slo: ''
                },

                editForm: {
                    errors: [],
                    name: '',
                    redirect: '',
                    trusted: false,
                    sso: true,
                    slo: ''
                }
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getClients();

                $('#modal-create-client').on('shown.bs.modal', () => {
                    $('#create-client-name').focus();
                });

                $('#modal-edit-client').on('shown.bs.modal', () => {
                    $('#edit-client-name').focus();
                });
            },

            /**
             * Get all of the OAuth clients for the user.
             */
            getClients() {
                axios.get('/oauth/clients')
                    .then(response => {
                        this.clients = response.data;
                    });
            },

            /**
             * Show the form for creating new clients.
             */
            showCreateClientForm() {
                $('#modal-create-client').modal('show');
            },

            /**
             * Create a new OAuth client for the user.
             */
            store() {
                this.persistClient(
                    'post', '/oauth/clients',
                    this.createForm, '#modal-create-client'
                );
            },

            /**
             * Edit the given client.
             */
            edit(client) {
                this.editForm.id = client.id;
                this.editForm.name = client.name;
                this.editForm.redirect = client.redirect;
                this.editForm.trusted = client.trusted;
                this.editForm.sso = client.sso;
                this.editForm.slo = client.slo;

                $('#modal-edit-client').modal('show');
            },

            /**
             * Update the client being edited.
             */
            update() {
                this.persistClient(
                    'put', '/oauth/clients/' + this.editForm.id,
                    this.editForm, '#modal-edit-client'
                );
            },

            /**
             * Persist the client to storage using the given form.
             */
            persistClient(method, uri, form, modal) {
                form.errors = [];

                axios[method](uri, form)
                    .then(response => {
                        this.getClients();

                        form.name = '';
                        form.redirect = '';
                        form.errors = [];

                        $(modal).modal('hide');
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            form.errors = _.flatten(_.toArray(error.response.data.errors));
                        } else {
                            form.errors = ['Something went wrong. Please try again.'];
                        }
                    });
            },

            /**
             * Destroy the given client.
             */
            destroy(client) {
                axios.delete('/oauth/clients/' + client.id)
                    .then(response => {
                        this.getClients();
                    });
            }
        }
    }
</script>
