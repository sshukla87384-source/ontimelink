@extends('layouts.app')

@section('title', 'Admin · Settings')

@section('content')
    <p class="otl-eyebrow mb-1">Admin</p>
    <h1 class="h3 mb-4">Platform settings</h1>

    @include('admin._nav')

    <div class="row">
        <div class="col-lg-7">
            <div class="card otl-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="site_announcement" class="form-label">Site announcement</label>
                            <textarea class="form-control @error('site_announcement') is-invalid @enderror" id="site_announcement"
                                      name="site_announcement" rows="3" maxlength="500"
                                      placeholder="Shown as a banner to all visitors. Leave blank to hide.">{{ old('site_announcement', $settings['site_announcement']) }}</textarea>
                            @error('site_announcement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label d-block">Registration</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="registration_open" id="reg-open" value="1"
                                       @checked(old('registration_open', $settings['registration_open']) == '1')>
                                <label class="form-check-label" for="reg-open">Open</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="registration_open" id="reg-closed" value="0"
                                       @checked(old('registration_open', $settings['registration_open']) == '0')>
                                <label class="form-check-label" for="reg-closed">Closed</label>
                            </div>
                            @error('registration_open')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-otl">Save settings</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card otl-card">
                <div class="card-header">Environment-managed settings</div>
                <div class="card-body small text-secondary">
                    Point economics (signup / referral bonuses, link cost), guest quota, bulk limits, and payment
                    gateway credentials are configured in <code>.env</code> so they can be audited and deployed
                    like code. See <code>docs/INSTALL.md</code> for the full variable reference.
                </div>
            </div>
        </div>
    </div>
@endsection
