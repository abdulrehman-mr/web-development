<x-admin-layout title="Email settings">
    <x-slot:actions><button form="email-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-3">
        <form id="email-form" method="post" enctype="multipart/form-data" action="{{ route('admin.email.update') }}" class="space-y-6 xl:col-span-2"
              x-data="{ driver: @js(old('mail_driver', setting('mail_driver', 'php'))), host: @js(old('mail_host', setting('mail_host'))), port: @js(old('mail_port', setting('mail_port', 587))), encryption: @js(old('mail_encryption', setting('mail_encryption', 'tls'))) }">
            @csrf @method('PUT')

            <x-admin.card title="Sending">
                <x-admin.toggle name="mail_enabled" label="Send emails" :checked="setting('mail_enabled', '1') === '1'" help="Master switch. When off, no automatic emails are sent at all. You can also switch individual emails on or off in Email templates."/>

                <p class="mt-6 mb-2 text-sm font-semibold text-slate-900">Sending method</p>
                <div class="grid gap-2">
                    @foreach ($drivers as $value => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl p-3 ring-1 transition" :class="driver === '{{ $value }}' ? 'bg-brand-50 ring-2 ring-brand-700' : 'ring-slate-200 hover:ring-slate-300'">
                            <input type="radio" name="mail_driver" value="{{ $value }}" x-model="driver" class="accent-brand-700">
                            <span class="text-sm font-medium text-slate-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p x-show="driver === 'php'" class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">Uses your server’s sendmail, exactly like PHP’s <code>mail()</code>. Works on most shared hosting with no setup, but emails are more likely to land in spam than with SMTP.</p>

                <div x-show="driver === 'smtp'" x-collapse class="mt-5 space-y-4">
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="self-center text-slate-500">Quick fill:</span>
                        @foreach (['Gmail' => ['smtp.gmail.com', 587, 'tls'], 'Outlook / Microsoft 365' => ['smtp.office365.com', 587, 'tls'], 'Zoho' => ['smtp.zoho.com', 465, 'ssl'], 'SendGrid' => ['smtp.sendgrid.net', 587, 'tls'], 'Mailgun' => ['smtp.mailgun.org', 587, 'tls'], 'Hostinger' => ['smtp.hostinger.com', 465, 'ssl']] as $provider => [$h, $p, $enc])
                            <button type="button" class="rounded-lg bg-white px-2.5 py-1 font-medium text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50" @click="host = '{{ $h }}'; port = {{ $p }}; encryption = '{{ $enc }}'">{{ $provider }}</button>
                        @endforeach
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2"><label for="f_mail_host" class="label">SMTP host</label><input id="f_mail_host" name="mail_host" x-model="host" class="field" placeholder="smtp.example.com"></div>
                        <div><label for="f_mail_port" class="label">Port</label><input id="f_mail_port" type="number" name="mail_port" x-model="port" class="field"></div>
                        <div>
                            <label for="f_mail_encryption" class="label">Encryption</label>
                            <select id="f_mail_encryption" name="mail_encryption" x-model="encryption" class="field">
                                <option value="tls">TLS / STARTTLS (port 587)</option>
                                <option value="ssl">SSL (port 465)</option>
                                <option value="none">None (port 25)</option>
                            </select>
                        </div>
                        <x-admin.input name="mail_username" label="Username" :value="setting('mail_username')" autocomplete="off"/>
                        <div>
                            <label for="f_mail_password" class="label">Password</label>
                            @php $saved = filled(\App\Models\Setting::secret('mail_password')); @endphp
                            <input id="f_mail_password" type="password" name="mail_password" autocomplete="new-password" class="field" placeholder="{{ $saved ? '•••••••• saved — leave blank to keep' : 'Not set' }}">
                            @if ($saved)<label class="mt-1.5 flex items-center gap-2 text-xs text-slate-500"><input type="checkbox" name="clear_mail_password" value="1" class="size-3.5 accent-rose-600"> Remove saved password</label>@endif
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">For Gmail, create an <em>App password</em> (Google Account → Security → App passwords) and use it here instead of your normal password.</p>
                </div>
            </x-admin.card>

            <x-admin.card title="Sender">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="mail_from_name" label="From name" :value="setting('mail_from_name', setting('site_name'))" required/>
                    <x-admin.input name="mail_from_address" type="email" label="From address" :value="setting('mail_from_address', setting('contact_email'))" required help="Use an address on your own domain so emails aren’t marked as spam."/>
                    <x-admin.input name="mail_reply_to" type="email" label="Reply-to address (optional)" :value="setting('mail_reply_to')" help="Where guests’ replies go. Defaults to your contact email."/>
                    <x-admin.input name="mail_admin_address" type="email" label="Send team notifications to" :value="setting('mail_admin_address')" :help="'New bookings, payments and enquiries. Defaults to '.(setting('contact_email') ?: 'your contact email').'.'"/>
                </div>
            </x-admin.card>

            <x-admin.card title="Email header & footer">
                <x-admin.image name="email_logo" label="Header logo" :value="setting('email_logo')" help="PNG or JPG with a transparent or white background works best. Leave empty to use the site logo, or your site name as text."/>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="email_logo_width" type="number" min="60" max="400" label="Logo width (pixels)" :value="setting('email_logo_width', 160)"/>
                    <div>
                        <label for="f_email_logo_align" class="label">Logo position</label>
                        <select id="f_email_logo_align" name="email_logo_align" class="field">
                            <option value="left" @selected(setting('email_logo_align', 'left') === 'left')>Left</option>
                            <option value="center" @selected(setting('email_logo_align') === 'center')>Centred</option>
                        </select>
                    </div>
                    <x-admin.textarea name="email_footer" label="Footer text (optional)" :value="setting('email_footer')" rows="3" class="sm:col-span-2" help="Shown above your contact details at the bottom of every email, e.g. your licence number."/>
                </div>
            </x-admin.card>
        </form>

        <div class="space-y-6">
            <x-admin.card title="Send a test email" description="Save your settings first, then send a test to check everything works.">
                <form method="post" action="{{ route('admin.email.test') }}" class="space-y-3">
                    @csrf
                    <input type="email" name="test_to" value="{{ old('test_to', auth()->user()->email) }}" class="field" required aria-label="Send test to">
                    @error('test_to')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                    <button class="btn btn-ghost w-full"><x-icon name="mail" class="size-4"/>Send test email</button>
                </form>
            </x-admin.card>
            <x-admin.card title="Email templates" description="Edit the wording of each automatic email and switch them on or off.">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-ghost w-full">Manage templates</a>
            </x-admin.card>
        </div>
    </div>
</x-admin-layout>
