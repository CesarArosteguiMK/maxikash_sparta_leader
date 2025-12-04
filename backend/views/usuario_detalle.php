<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
        <div class="mb-2 mb-sm-0">
            <h4 class="mb-1">JONATHAN VALDEZ MARTINEZ </h4>
            <p class="mb-0"><span class="badge bg-label-primary">Fecha de Alta: 03/06/2025 14:20:00</span></p>
        </div>
        <button type="button" class="btn btn-label-danger delete-customer">Dar de Baja al Colaborador</button>
    </div>

    <div class="row">
        <!-- Customer-detail Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- Customer-detail Card -->
            <div class="card mb-6">
                <div class="card-body pt-12">
                    <div class="customer-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <img class="img-fluid rounded mb-4" src="   https://cdn-icons-png.flaticon.com/512/16683/16683419.png " width="120" height="120" alt="" title="" class="img-small">
                            <div class="customer-info text-center mb-6">
                                <h5 class="mb-0">JONATHAN VALDEZ MARTINEZ</h5>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-around flex-wrap mb-6 gap-0 gap-md-3 gap-lg-4">
                        <div class="d-flex align-items-center gap-4 me-5">
                            <div class="avatar">
                                <div class="avatar-initial rounded bg-label-primary"><i class="fa fa-hashtag"></i></div>
                            </div>
                            <div>
                                <h5 class="mb-0">184</h5>
                                <span>Solicitudes</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="avatar">
                                <div class="avatar-initial rounded bg-label-primary"><i class="fa fa-dollar"></i></div>
                            </div>
                            <div>
                                <h5 class="mb-0">$12,378</h5>
                                <span>Total Comprobado</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-container">
                        <h5 class="pb-4 border-bottom text-capitalize mt-6 mb-4">Detalles</h5>
                        <ul class="list-unstyled mb-6">
                            <li class="mb-2">
                                <span class="h6 me-1">Usuario:</span>
                                <span class="badge bg-label-pinterest">VAMM102827RF</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-1">CURP:</span>
                                <span>VAMM102827RFCRLY07</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-1">RFC:</span>
                                <span>VAMM102827RF</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Fecha de Nacimiento:</span>
                                <span>26/10/1994</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Email Personal:</span>
                                <span>ejemplo@gmail.com</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-1">Estatus:</span>
                                <span class="badge bg-label-success">Activo</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-1">Reporta comprobaciones a:</span>
                                <span class="badge bg-label-primary">ANGEL MOISES GUERRERO</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Nómina Base:</span>
                                <span class="badge bg-label-info">FINANCIERA CULTIVA</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Puesto del colaborador:</span>
                                <span class="">OPERACIONES</span>
                            </li>


                            <li class="mb-2">
                                <span class="h6 me-1"># Nómina:</span>
                                <span>0640</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Región:</span>
                                <span>REGIÓN NEVADO (FINANCIERA CULTIVA)</span>
                            </li>

                            <li class="mb-2">
                                <span class="h6 me-1">Sucursal:</span>
                                <span>TOLUCA 1</span>
                            </li>


                        </ul>
                        <div class="d-flex justify-content-center">
                            <a href="javascript:;" class="btn btn-primary w-100" data-bs-target="#editUser" data-bs-toggle="modal">Editar Detalles</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Customer-detail Card -->

        </div>
        <!--/ Customer Sidebar -->

        <!-- Customer Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <!-- Customer Pills -->
            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row mb-6 row-gap-2 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i class="fa fa-lock"></i> &nbsp;Seguridad y Permisos</a>
                    </li>

                </ul>
            </div>
            <!--/ Customer Pills -->
            <!-- Change Password -->
            <div class="card mb-6">
                <h5 class="card-header">
                    Cambiar la contraseña</h5>
                <div class="card-body">
                    <form id="formChangePassword" method="GET" onsubmit="return false" class="fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
                        <div class="alert alert-warning alert-dismissible py-3" role="alert">
                            <h5 class="alert-heading mb-1">Asegúrese de que se cumplan estos requisitos.</h5>
                            <span>Mínimo 8 caracteres, mayúsculas y símbolos.</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="row gy-4 gx-6">
                            <div class="col-12 col-sm-6 form-password-toggle form-control-validation fv-plugins-icon-container">
                                <label class="form-label" for="newPassword">Nueva Contraseña</label>
                                <div class="input-group input-group-merge has-validation">
                                    <input class="form-control" type="password" id="newPassword" name="newPassword" placeholder="············">
                                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                                </div><div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>

                            <div class="col-12 col-sm-6 form-password-toggle form-control-validation fv-plugins-icon-container">
                                <label class="form-label" for="confirmPassword">Confirmar Nueva Contraseña</label>
                                <div class="input-group input-group-merge has-validation">
                                    <input class="form-control" type="password" name="confirmPassword" id="confirmPassword" placeholder="············">
                                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                                </div><div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">Guardar Cambios</button>
                            </div>
                        </div>
                        <input type="hidden"></form>
                </div>
            </div>
            <!--/ Change Password -->
            <div class="card mb-6">
                <!-- Notifications -->
                <div class="card-header">
                    <h5 class="card-title mb-1">Empresas</h5>
                    <span class="card-subtitle">Indique las empresas con las que está relacionado el colaborador, a las cuales podrá cargar comprobaciones.</span>
                </div>
                <div>
                    <div class="table-responsive border-bottom">
                        <table class="table">
                            <thead>
                            <tr>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="text-nowrap text-heading"><img src="/assets/img/logo_mcm.png" alt="facebook" class="me-5" height="40"> Más Con Menos</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="defaultCheck4" checked="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap text-heading"><img src="/assets/img/logo_cultiva.png" alt="facebook" class="me-5" height="40"> Financiera Cultiva</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="defaultCheck4" checked="">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap text-heading"><img src="/assets/img/logo_productora.png" alt="facebook" class="me-4" height="36"> Productora Cultiva</td>
                                <td>
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="defaultCheck7" checked="">
                                    </div>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <button type="submit" class="btn btn-primary me-3">Guardar Cambios</button>
                    <button type="reset" class="btn btn-label-secondary">Descartar Cambios</button>
                </div>
                <!-- /Notifications -->
            </div>
        </div>
        <!--/ Customer Content -->
    </div>

    <!-- Modal -->
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Edit User Information</h4>
                        <p>Updating user details will receive a privacy audit.</p>
                    </div>
                    <form class="row g-6" onsubmit="return false">
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="modalEditUserFirstName">Nombre(s)</label>
                            <input type="text" id="modalEditUserFirstName" name="modalEditUserFirstName" class="form-control" placeholder="John" value="John">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="modalEditUserLastName">Apellido Paterno</label>
                            <input type="text" id="modalEditUserLastName" name="modalEditUserLastName" class="form-control" placeholder="Doe" value="Doe">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="modalEditUserLastName">Apellido Materno</label>
                            <input type="text" id="modalEditUserLastName" name="modalEditUserLastName" class="form-control" placeholder="Doe" value="Doe">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="modalEditUserName">Usuario</label>
                            <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-control" placeholder="johndoe007" value="johndoe007">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserEmail">Email</label>
                            <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-control" placeholder="example@domain.com" value="example@domain.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserStatus">Status</label>
                            <div class="position-relative"><select id="modalEditUserStatus" name="modalEditUserStatus" class="select2 form-select select2-hidden-accessible" aria-label="Default select example" data-select2-id="modalEditUserStatus" tabindex="-1" aria-hidden="true">
                                    <option selected="" data-select2-id="2">Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                    <option value="3">Suspended</option>
                                </select><span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="1" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-modalEditUserStatus-container"><span class="select2-selection__rendered" id="select2-modalEditUserStatus-container" role="textbox" aria-readonly="true" title="Status">Status</span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditTaxID">Tax ID</label>
                            <input type="text" id="modalEditTaxID" name="modalEditTaxID" class="form-control modal-edit-tax-id" placeholder="123 456 7890" value="123 456 7890">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">US (+1)</span>
                                <input type="text" id="modalEditUserPhone" name="modalEditUserPhone" class="form-control phone-number-mask" placeholder="202 555 0111" value="202 555 0111">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserLanguage">Language</label>
                            <div class="position-relative"><select id="modalEditUserLanguage" name="modalEditUserLanguage" class="select2 form-select select2-hidden-accessible" multiple="" data-select2-id="modalEditUserLanguage" tabindex="-1" aria-hidden="true">
                                    <option value="">Select</option>
                                    <option value="english" selected="" data-select2-id="4">English</option>
                                    <option value="spanish">Spanish</option>
                                    <option value="french">French</option>
                                    <option value="german">German</option>
                                    <option value="dutch">Dutch</option>
                                    <option value="hebrew">Hebrew</option>
                                    <option value="sanskrit">Sanskrit</option>
                                    <option value="hindi">Hindi</option>
                                </select><span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="3" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--multiple" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="-1" aria-disabled="false"><ul class="select2-selection__rendered"><li class="select2-selection__choice" title="English" data-select2-id="5"><span class="select2-selection__choice__remove" role="presentation">×</span>English</li><li class="select2-search select2-search--inline"><input class="select2-search__field" type="search" tabindex="0" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" role="searchbox" aria-autocomplete="list" placeholder="" style="width: 0.75em;"></li></ul></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span></div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserCountry">Country</label>
                            <div class="position-relative"><select id="modalEditUserCountry" name="modalEditUserCountry" class="select2 form-select select2-hidden-accessible" data-allow-clear="true" data-select2-id="modalEditUserCountry" tabindex="-1" aria-hidden="true">
                                    <option value="">Select</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="Canada">Canada</option>
                                    <option value="China">China</option>
                                    <option value="France">France</option>
                                    <option value="Germany">Germany</option>
                                    <option value="India" selected="" data-select2-id="7">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Korea">Korea, Republic of</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Russia">Russian Federation</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                </select><span class="select2 select2-container select2-container--default" dir="ltr" data-select2-id="6" style="width: auto;"><span class="selection"><span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-modalEditUserCountry-container"><span class="select2-selection__rendered" id="select2-modalEditUserCountry-container" role="textbox" aria-readonly="true" title="India"><span class="select2-selection__clear" title="Remove all items" data-select2-id="8">×</span>India</span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch my-2 ms-2">
                                <input type="checkbox" class="form-check-input" id="editBillingAddress" checked="">
                                <label for="editBillingAddress" class="switch-label">Use as a billing address?</label>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Edit User Modal -->

    <!-- Enable OTP Modal -->
    <div class="modal fade" id="enableOTP" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-simple modal-enable-otp modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Enable One Time Password</h4>
                        <p>Verify Your Mobile Number for SMS</p>
                    </div>
                    <p>Enter your mobile phone number with country code and we will send you a verification code.</p>
                    <form id="enableOTPForm" class="row g-6 fv-plugins-bootstrap5 fv-plugins-framework" onsubmit="return false" novalidate="novalidate">
                        <div class="col-12 form-control-validation fv-plugins-icon-container">
                            <label class="form-label" for="modalEnableOTPPhone">Phone Number</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">US (+1)</span>
                                <input type="text" id="modalEnableOTPPhone" name="modalEnableOTPPhone" class="form-control phone-number-otp-mask" placeholder="202 555 0111">
                            </div><div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-3">Send OTP</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        </div>
                        <input type="hidden"></form>
                </div>
            </div>
        </div>
    </div>
    <!--/ Enable OTP Modal -->

    <!-- Add New Credit Card Modal -->
    <div class="modal fade" id="upgradePlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-simple modal-upgrade-plan">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-6">
                        <h4 class="mb-2">Upgrade Plan</h4>
                        <p>Choose the best plan for user.</p>
                    </div>
                    <form id="upgradePlanForm" class="row g-4" onsubmit="return false">
                        <div class="col-sm-9">
                            <label class="form-label" for="choosePlan">Choose Plan</label>
                            <select id="choosePlan" name="choosePlan" class="form-select" aria-label="Choose Plan">
                                <option selected="">Choose Plan</option>
                                <option value="standard">Standard - $99/month</option>
                                <option value="exclusive">Exclusive - $249/month</option>
                                <option value="Enterprise">Enterprise - $499/month</option>
                            </select>
                        </div>
                        <div class="col-sm-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Upgrade</button>
                        </div>
                    </form>
                </div>
                <hr class="mx-5 my-2">
                <div class="modal-body">
                    <p class="mb-0">User current plan is standard plan</p>
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex justify-content-center me-2 mt-1">
                            <sup class="h6 pricing-currency pt-1 mt-2 mb-0 me-1 text-primary fw-normal">$</sup>
                            <h1 class="mb-0 text-primary">99</h1>
                            <sub class="pricing-duration mt-auto mb-5 pb-1 small text-body">/month</sub>
                        </div>
                        <button class="btn btn-label-danger cancel-subscription">Cancel Subscription</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ Add New Credit Card Modal -->

    <!-- /Modal -->
</div>