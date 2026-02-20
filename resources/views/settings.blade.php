<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/img/abosaleh-logo.png">
    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/modal.css">
    <link rel="stylesheet" href="/css/confirmModal.css">
    <link rel="stylesheet" href="/css/alert.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/settings.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">
            @if (session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">X</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            <section class="settings-page" aria-label="Settings page">
                <section class="dashboard-card settings-page__card">
                    <header class="settings-page__header">
                        <h2 class="settings-page__title">Settings</h2>
                        <p class="settings-page__subtitle">Manage company, users, defaults, and security.</p>
                    </header>
                    <div class="settings-accordion" aria-label="Settings sections">
                        {{-- 1) Users & Team --}}
                        @php
                        $i=1;
                        @endphp
                        @if($currentUserRole === 'owner')
                        <article class="settings-section" data-acc>
                            <button class="settings-section__toggle" type="button" aria-expanded="false">
                                <span class="settings-section__toggle-text">{{$i++}}. User & Team Management (Owner
                                    Only)</span>
                                <span class="settings-section__chev" aria-hidden="true">▾</span>
                            </button>

                            <div class="settings-section__panel" role="region">
                                <div class="settings-section__content">
                                    <ul class="settings-list">
                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Employee Profiles</div>
                                            <div class="settings-list__desc">List staff members with contact info and
                                                ID.</div>
                                            <div class="settings-actions">
                                                <button type="button" class="settings-btn"
                                                    data-modal-open="employeesModal">
                                                    View employees
                                                </button>

                                            </div>
                                        </li>

                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Add New User</div>
                                            <div class="settings-list__desc">Invite new employees to the platform.</div>
                                            <div class="settings-actions">
                                                <button class="settings-btn" id="inviteEmployeeBtn"
                                                    data-modal-open="inviteEmployeeModal">
                                                    Invite employee
                                                </button>

                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </article>
                        @endif
                        <article class="settings-section" data-acc>
                            <button class="settings-section__toggle" type="button" aria-expanded="false">
                                <span class="settings-section__toggle-text">{{$i++}}. Profile Management</span>
                                <span class="settings-section__chev" aria-hidden="true">▾</span>
                            </button>

                            <div class="settings-section__panel" role="region">
                                <div class="settings-section__content">
                                    <ul class="settings-list">
                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Edit Profile</div>
                                            <div class="settings-list__desc">Edit your profile information.</div>
                                            <div class="settings-actions">
                                                <button type="button" class="settings-btn"
                                                    data-modal-open="editProfileModal">
                                                    Edit Profile
                                                </button>

                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </article>
                        {{-- 4) System Logs & Security --}}
                        <article class="settings-section" data-acc>
                            <button class="settings-section__toggle" type="button" aria-expanded="false">
                                <span class="settings-section__toggle-text">{{$i++}}. System Logs & Security</span>
                                <span class="settings-section__chev" aria-hidden="true">▾</span>
                            </button>

                            <div class="settings-section__panel" role="region">
                                <div class="settings-section__content">
                                    <ul class="settings-list">
                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Activity Audit Log</div>
                                            <div class="settings-list__desc">Track changes to sensitive data (who
                                                changed what and when).</div>
                                            <div class="settings-actions">
                                                <button type="button" class="settings-btn"
                                                    data-modal-open="auditLogModal">
                                                    View audit log
                                                </button>

                                            </div>
                                        </li>
                                        @if($currentUserRole === 'owner')
                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Password Management</div>
                                            <div class="settings-list__desc">Reset employee passwords or force logout.
                                            </div>

                                            <div class="settings-actions">
                                                <button type="button" class="settings-btn"
                                                    data-modal-open="managePasswordsModal">

                                                    Manage passwords
                                                </button>

                                            </div>
                                            @endif
                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Reset Password</div>
                                            <div class="settings-list__desc">Reset your password.
                                            </div>

                                            <div class="settings-actions">
                                                <button type="button" class="settings-btn"
                                                    data-modal-open="resetPasswordsModal">
                                                    Reset password
                                                </button>

                                            </div>
                                        </li>

                                        <li class="settings-list__item">
                                            <div class="settings-list__title">Backup & Export</div>
                                            <div class="settings-list__desc">Download all client and apartment data
                                                (Excel/CSV).</div>
                                            <div class="settings-actions">
                                                <a href="{{ route('settings.export') }}"
                                                    class="settings-btn settings-btn--primary">Export data</a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </section>
            {{-- Employees Modal --}}
            <x-modal id="employeesModal" title="Employees">
                <div class="settings-employees">
                    <div class="settings-employees__toolbar">
                        <div class="settings-employees__search">
                            <span class="settings-employees__search-icon" aria-hidden="true">🔎</span>
                            <input id="employeesSearch" type="text" placeholder="Search by name, ID, phone..." />
                        </div>

                        <button type="button" class="settings-employees__btn settings-employees__btn--primary"
                            data-modal-open="inviteEmployeeModal">
                            + Invite employee
                        </button>
                    </div>



                    <div class="settings-employees__table-wrap">
                        <table class="settings-employees__table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th class="settings-employees__hide-sm">Phone</th>
                                    <th class="settings-employees__hide-sm">Email</th>
                                    <th>Role</th>
                                    <th class="settings-employees__actions-col">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTbody">
                                @foreach($employees as $emp)
                                <tr data-id="{{ $emp['id'] }}" data-name="{{ $emp['name'] }}"
                                    data-phone="{{ $emp['phone'] }}" data-email="{{ $emp['email'] }}"
                                    data-role="{{ $emp['role'] }}"
                                    data-search="{{ strtolower($emp['id'].' '.$emp['name'].' '.$emp['phone'].' '.$emp['email'].' '.$emp['role']) }}">
                                    <td>EMP-{{ str_pad( $emp['id'],5,'0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $emp['name'] }}</td>
                                    <td class="settings-employees__hide-sm">{{ $emp['phone'] }}</td>
                                    <td class="settings-employees__hide-sm">{{ $emp['email'] }}</td>
                                    <td><span class="settings-employees__pill">{{ $emp['role'] }}</span></td>
                                    <td class="settings-employees__actions-col">
                                        <button type="button" class="settings-employees__icon-btn--edit"
                                            aria-label="Edit" data-modal-open="editEmployeeModal">✎</button>
                                        <button type="button"
                                            class="settings-employees__icon-btn settings-employees__icon-btn--danger"
                                            aria-label="Delete" data-delete="{{$emp['id'] }}">🗑</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-modal>
            {{-- Adding Employees Modal --}}
            <x-modal id="inviteEmployeeModal" title="Add employee">
                <form class="invite-employee-form" action="{{ route('employees.add') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Avatar upload + preview --}}
                    <div class="form-field">
                        <label class="form-label" for="emp_avatar">Avatar (optional)</label>

                        <div class="emp-avatar">
                            <div class="emp-avatar__preview" aria-hidden="true">
                                <img id="emp_avatar_preview" src="/img/avatar-placeholder.png" alt="" />
                            </div>

                            <div class="emp-avatar__controls">
                                <input id="emp_avatar" name="emp_avatar" type="file" accept="image/*">
                                <div class="emp-avatar__hint">PNG/JPG/WebP • square works best</div>
                            </div>
                        </div>
                        @error('emp_avatar') <p style="color:red">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label for="emp_name">Full name</label>
                        <input id="emp_name" name="emp_name" type="text" placeholder="John Doe" required>
                    </div>

                    <div class="form-field">
                        <label for="emp_phone">Phone</label>
                        <input id="emp_phone" type="tel" name="emp_phone" placeholder="+961 70 000 000" required>
                    </div>

                    <div class="form-field">
                        <label for="emp_email">Email</label>
                        <input id="emp_email" type="email" name="emp_email" placeholder="employee@email.com" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-ghost">Create employee</button>
                        <button type="button" class="btn-ghost modal-close"
                            data-modal-close="inviteEmployeeModal">Cancel</button>
                    </div>
                </form>
            </x-modal>
            {{-- Edit Profile Modal --}}
            <x-modal id="editProfileModal" title="Edit profile">
                <form class="invite-employee-form" action="{{ route('employees.editAvatar') }}"
                    enctype="multipart/form-data" method="post">
                    @csrf
                    <input type="hidden" value="{{ auth()->id() }}" name="profile_id">
                    {{-- Avatar upload + preview --}}
                    <div class="form-field">
                        <label class="form-label" for="profile_avatar">Avatar </label>

                        <div class="emp-avatar">
                            <div class="emp-avatar__preview" aria-hidden="true">
                                <img id="emp_avatar_preview" src="{{ auth()->user()->avatar}}" alt="" />
                            </div>

                            <div class="emp-avatar__controls">
                                <input id="profile_avatar" name="profile_avatar" type="file" accept="image/*">
                                <div class="emp-avatar__hint">PNG/JPG/WebP • square works best</div>
                            </div>
                        </div>
                        @error('profile_avatar') <p style="color:red">{{ $message }}</p> @enderror
                    </div>


                    <div class="form-actions">
                        <button type="submit" class="btn-ghost">Edit profile</button>
                        <button type="button" class="btn-ghost modal-close"
                            data-modal-close="editProfileModal">Cancel</button>
                    </div>
                </form>
            </x-modal>
            {{-- Editing Employees Modal --}}
            <x-modal id="editEmployeeModal" title="Edit employee">
                <form class="invite-employee-form" action="{{ route('employees.edit') }}" method="post">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="employee_id" id="edit_emp_id">
                    <div class="form-field">
                        <label for="edit_emp_name">Full name</label>
                        <input id="edit_emp_name" name="edit_emp_name" type="text" placeholder="John Doe" required>
                    </div>

                    <div class="form-field">
                        <label for="edit_emp_phone">Phone</label>
                        <input id="edit_emp_phone" type="tel" name="edit_emp_phone" placeholder="+961 70 000 000"
                            required>
                    </div>

                    <div class="form-field">
                        <label for="edit_emp_email">Email</label>
                        <input id="edit_emp_email" type="email" name="edit_emp_email" placeholder="employee@email.com"
                            required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-ghost">
                            Edit employee
                        </button>

                        <button type="button" class="btn-ghost modal-close" data-modal-close="editEmployeeModal">
                            Cancel
                        </button>
                    </div>

                </form>
            </x-modal>
            {{-- audit log modal --}}
            <x-modal id="auditLogModal" title="Activity audit log">
                <section class="audit-log" aria-label="Audit log modal">

                    {{-- Toolbar --}}
                    <div class="audit-log__toolbar">
                        <div class="audit-log__search">
                            <span class="audit-log__search-icon" aria-hidden="true">🔎</span>
                            <input id="auditSearch" type="text"
                                placeholder="Search user, action, entity, record, details..." />
                        </div>

                        <div class="audit-log__filters">
                            <select id="auditFilterAction" class="audit-log__select" aria-label="Filter by action">
                                <option value="">All actions</option>
                                <option value="create">Create</option>
                                <option value="update">Update</option>
                                <option value="delete">Delete</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                            </select>

                            <select id="auditFilterEntity" class="audit-log__select" aria-label="Filter by entity">
                                <option value="">All entities</option>
                                <option value="client">Client</option>
                                <option value="payment">Payment</option>
                                <option value="project">Project</option>
                                <option value="unit">Unit</option>
                                <option value="inventory">Inventory</option>
                                <option value="user">User</option>
                                <option value="settings">Settings</option>
                            </select>

                            <div class="audit-log__dates">
                                <div class="audit-log__date">
                                    <label class="audit-log__date-label" for="auditFrom">From</label>
                                    <input id="auditFrom" class="audit-log__date-input" type="date" />
                                </div>
                                <div class="audit-log__date">
                                    <label class="audit-log__date-label" for="auditTo">To</label>
                                    <input id="auditTo" class="audit-log__date-input" type="date" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="audit-log__table-wrap" aria-label="Audit log table">
                        <table class="audit-log__table">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Record</th>
                                    <th>Details</th>
                                </tr>
                            </thead>

                            <tbody id="auditTbody">
                                @foreach($auditLogs as $audit)
                                @php
                                $whenDate = explode(' ', $audit->created_at)[0]; // YYYY-MM-DD
                                $search = strtolower($audit->created_at.' '.$audit->user->name.' '.$audit->action.'
                                '.$audit->entity_type.' '.' '.$audit->details);
                                @endphp

                                <tr class="audit-log__row" data-search="{{ $search }}" data-action="{{ $audit->event }}"
                                    data-entity="{{ $audit->entity_type }}" data-date="{{ $whenDate }}">
                                    <td>{{ $audit->created_at }}</td>
                                    <td>{{ $audit->user->name }}</td>
                                    <td>
                                        <span class="audit-log__pill audit-log__pill--{{ $audit->event }}">
                                            {{ strtoupper($audit->event) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($audit->entity_type) }}</td>
                                    <td class="audit-log__mono">{{ $audit->record }}</td>
                                    <td class="audit-log__details">{{ $audit->details }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer actions (optional) --}}
                    <div class="audit-log__footer">
                        <button type="button" class="btn-ghost modal-close"
                            data-modal-close="auditLogModal">Close</button>
                        <button type="button" class="btn-ghost excel" id="auditExportExcelBtn">Export as excel</button>
                        <button type="button" class="btn-ghost pdf" id="auditPrintBtn">Print / Save PDF</button>

                    </div>

                </section>
            </x-modal>
            {{-- manage passwords modal --}}
            <x-modal id="managePasswordsModal" title="Manage passwords">
                <section class="pwd-modal" aria-label="Manage passwords modal">

                    <div class="pwd-modal__toolbar">
                        <div class="pwd-modal__search">
                            <span class="pwd-modal__search-icon" aria-hidden="true">🔎</span>
                            <input id="pwdSearch" type="text" placeholder="Search by name, ID, phone, email..." />
                        </div>
                    </div>
                    <div class="pwd-modal__table-wrap">
                        <table class="pwd-modal__table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th class="pwd-modal__hide-sm">Phone</th>
                                    <th class="pwd-modal__hide-sm">Email</th>
                                    <th>Role</th>
                                    <th class="pwd-modal__actions-col">Actions</th>
                                </tr>
                            </thead>

                            <tbody id="pwdTbody">
                                @foreach($employees as $emp)
                                @php
                                $search = strtolower($emp['id'].' '.$emp['name'].' '.$emp['phone'].' '.$emp['email'].'
                                '.$emp['role']);
                                @endphp

                                <tr data-search="{{ $search }}">
                                    <td class="pwd-modal__mono">EMP-{{ str_pad( $emp['id'],5,'0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $emp['name'] }}</td>
                                    <td class="pwd-modal__hide-sm">{{ $emp['phone'] }}</td>
                                    <td class="pwd-modal__hide-sm">{{ $emp['email'] }}</td>
                                    <td><span class="pwd-modal__pill">{{ $emp['role'] }}</span></td>
                                    <td class="pwd-modal__actions-col">
                                        <button type="button" class="pwd-modal__btn pwd-modal__btn--reset"
                                            data-open-reset="{{ $emp['id'] }}" data-emp-name="{{ $emp['name'] }}">
                                            Reset
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Reset panel (inline, no extra modal, keeps UX fast) --}}
                    <div class="pwd-reset" id="pwdResetPanel" hidden>
                        <div class="pwd-reset__head">
                            <div>
                                <div class="pwd-reset__title">Reset password</div>
                                <div class="pwd-reset__sub">
                                    Employee: <span id="pwdResetEmpLabel" class="pwd-reset__mono"></span>
                                </div>
                            </div>


                        </div>

                        <form class="pwd-reset__form" action="{{ route('employees.editPassword') }}" method="post">
                            @csrf
                            <input type="hidden" id="ResetEmpId" name="employee_id" value="">
                            <input type="hidden" name="mode" value="admin">
                            <div class="pwd-reset__grid">
                                <div class="pwd-reset__field">
                                    <label class="pwd-reset__label" for="new_password">New password</label>

                                    <div class="password-field">
                                        <input id="new_password" name="new_password" type="password"
                                            placeholder="••••••••" required>
                                        <button type="button" class="password-toggle"
                                            aria-label="Toggle password visibility">👁</button>
                                    </div>
                                    @error('new_password')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="pwd-reset__field">
                                    <label class="pwd-reset__label" for="confirm_password">Confirm password</label>

                                    <div class="password-field">
                                        <input id="confirm_password" name="new_password_confirmation" type="password"
                                            placeholder="••••••••" required>
                                        <button type="button" class="password-toggle"
                                            aria-label="Toggle password visibility">👁</button>
                                    </div>
                                    @error('new_password_confirmation')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="pwd-reset__actions">
                                <button type="submit" class="btn-primary btn-ghost">Save</button>
                                <button type="button" class="btn-ghost" id="pwdResetCancelBtn">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div class="pwd-modal__footer">
                        <button type="button" class="btn-ghost modal-close"
                            data-modal-close="managePasswordsModal">Close</button>
                    </div>

                </section>
            </x-modal>
            {{-- reset passwords modal --}}
            <x-modal id="resetPasswordsModal" title="Reset passwords">
                <section class="pwd-modal" aria-label="Reset passwords modal">
                    <div class="pwd-reset" id="pwdResetPanel" hidden>
                        <form class="pwd-reset__form" action="{{ route('employees.editPassword') }}" method="post">
                            @csrf
                            <input type="hidden" name="mode" value="self">
                            <input type="hidden" id="pwdResetEmpId" name="employee_id" value="{{ auth()->id() }}">
                            <div class="pwd-reset__grid">
                                <div class="pwd-reset__field">
                                    <label class="pwd-reset__label" for="old_password">Old password</label>
                                    <div class="password-field">
                                        <input id="old_password" name="old_password" type="password"
                                            placeholder="••••••••" required>
                                        <button type="button" class="password-toggle"
                                            aria-label="Toggle password visibility">👁</button>

                                    </div>
                                    @error('old_password')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div><br>
                                <div class="pwd-reset__field">
                                    <label class="pwd-reset__label" for="new_password">New password</label>
                                    <div class="password-field">
                                        <input id="new_password" name="new_password" type="password"
                                            placeholder="••••••••" required>
                                        <button type="button" class="password-toggle"
                                            aria-label="Toggle password visibility">👁</button>

                                    </div>
                                    @error('new_password')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="pwd-reset__field">
                                    <label class="pwd-reset__label" for="confirm_password">Confirm password</label>
                                    <div class="password-field">
                                        <input id="confirm_password" name="new_password_confirmation" type="password"
                                            placeholder="••••••••" required>
                                        <button type="button" class="password-toggle"
                                            aria-label="Toggle password visibility">👁</button>

                                    </div>
                                    @error('new_password_confirmation')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="pwd-reset__actions">
                                <button type="submit" class="btn-primary btn-ghost">Save</button>
                                <button type="button" class="btn-ghost" id="pwdResetCancelBtn">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div class="pwd-modal__footer">
                        <button type="button" class="btn-ghost modal-close"
                            data-modal-close="resetPasswordsModal">Close</button>
                    </div>

                </section>
            </x-modal>
            <div class="confirm-modal" id="confirmEmployeeDeleteModal">
                <div class="confirm-modal__backdrop"></div>

                <div class="confirm-modal__box">
                    <h3 class="confirm-modal__title">Delete Employee</h3>
                    <p class="confirm-modal__text">
                        Are you sure you want to delete this employee?
                        <br>This action <strong>cannot be undone</strong>.
                    </p>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel"
                            onclick="closeEmployeeConfirmModal()">
                            Cancel
                        </button>

                        <button type="button" class="confirm-modal__btn confirm-modal__btn--danger"
                            id="confirmEmployeeDeleteBtn">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="/js/settings.js"></script>
    <script src="/js/navSearch.js"></script>

</body>

</html>