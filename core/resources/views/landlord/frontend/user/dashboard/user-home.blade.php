@extends('landlord.frontend.user.dashboard.user-master')
@section('page-title')
    {{__('User Home')}}
@endsection

@section('title')
    {{__('User Home')}}
@endsection

@section('style')
    <style>
        .badge {
            font-size: 15px;
        }
    </style>
    <style>
        .payment_getway_image ul {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            align-items: stretch;
        }
        .payment_getway_image ul li {
            width: calc(100% / 5 - 8px);
            transition: 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            border-color: #ddd;
            overflow: hidden;
            height: 50px;
        }
        .payment_getway_image ul li:is(:hover, .selected){
            border: 2px solid red;
        }
    </style>

    <style>
        .text-center .confirm-details--icon {
            margin-inline: auto;
        }
        .confirm-details--icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 50px;
            width: 50px;
            border-radius: 50%;
            background-color: var(--main-color-three);
            color: #fff;
            font-size: 24px;
        }
        .confirm-details--title {
            font-size: 24px;
            font-weight: 600;
            line-height: 1.2;
            color: var(--heading-color);
        }
        .confirm-details--para {
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            color: var(--paragraph-color);
            text-align: left;
        }
        .confirm-details--para span:first-child{
            font-weight: 800;
        }
    </style>

@endsection

@section('section')
    @php
        $auth_user = Auth::guard('web')->user();
    @endphp
    <div class="row g-4">
        <div class="col-md-12">
            <div class="btn-wrapper mb-3 mt-2" style="float: right">
                <a href="javascript:void(0)" class="cmn-btn cmn-btn-bg-1 cmn-btn-small mx-2"
                   data-bs-toggle="modal"
                   data-bs-target="#user_add_subscription"
                >{{__('Create Shop')}}</a>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 orders-child">
            <div class="single-orders">
                <div class="orders-flex-content">
                    <div class="icon">
                        <i class="las la-tasks"></i>
                    </div>
                    <div class="contents">
                        <h2 class="order-titles"> {{$package_orders ?? ''}} </h2>
                        <span class="order-para">{{__('Total Orders')}} </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 orders-child">
            <div class="single-orders">

                <div class="orders-flex-content">
                    <div class="icon">
                        <i class="las la-tasks"></i>
                    </div>
                    <div class="contents">
                        <h2 class="order-titles"> {{$support_tickets ?? ''}} </h2>
                        <span class="order-para">{{__('Support Tickets')}} </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="subdomains custom_domain_wrap mt-4">
                <h4 class="custom_domain_title">{{__('Your Shops')}}</h4>
                <div class="payment custom_domain_table mt-4">
                    <table class="table table-bordered recent_payment_table">
                        <thead>
                        <th>{{__('ID')}}</th>
                        <th>{{__('Site')}}</th>
                        <th>{{__('Browse')}}</th>
                        </thead>
                        <tbody class="w-100">
                        @php
                            $user = Auth::guard('web')->user();
                        @endphp

                        @foreach($user->tenant_details ?? [] as $key => $data)
                            @php
                                $url = '';
                                $central = '.'.env('CENTRAL_DOMAIN');

                                if(!empty($data->custom_domain?->custom_domain) && $data->custom_domain?->custom_domain_status == 'connected'){
                                    $custom_url = $data->custom_domain?->custom_domain ;
                                    $url = tenant_url_with_protocol($custom_url);
                                }else{
                                    $local_url = $data->id .$central ;
                                    $url = tenant_url_with_protocol($local_url);
                                }

                                $hash_token = hash_hmac('sha512',$user->username.'_'.$data->id, $data->unique_key);
                            @endphp

                            <tr>
                                <td>{{$key +1}}</td>
                                <td>{{$url}}</td>
                                <td>
                                    <a class="badge rounded bg-primary px-4 visitweb"
                                       href="{{tenant_url_with_protocol(optional($data->domain)->domain)}}" target="_blank">{{__('Visit Website')}}</a>
                                    <a class="badge rounded bg-danger px-4" href="{{$url.'/token-login/'.$hash_token}}" target="_blank">{{__('Login as Super Admin')}}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="custom_domain_wrap mt-4">
                <h4 class="custom_domain_title">{{__('Recent Orders')}}</h4>
                <div class="payment custom_domain_table mt-4">
                    <table class="table table-bordered recent_payment_table">
                        <thead>
                        <th>{{__('ID')}}</th>
                        <th>{{__('Order Name')}}</th>
                        <th>{{__('Package Name')}}</th>
                        <th>{{__('Amount')}}</th>
                        <th>{{__('Payment Status')}}</th>
                        <th>{{__('Start Date')}}</th>
                        <th>{{__('Expire Date')}}</th>
                        <th>{{__('Renew Taken')}}</th>
                        </thead>
                        <tbody class="w-100">
                        @foreach($recent_logs as $key=> $data)
                            <tr>
                                <td>{{$key +1}}</td>
                                <td>{{$data?->domain?->domain ?? __('Unsuccessful Transaction')}}</td>
                                <td>{{$data->package_name}}</td>
                                <td>{{ amount_with_currency_symbol($data->package_price) }}</td>
                                <td>{{ $data->payment_status }}</td>
                                <td>{{date('d-m-Y', strtotime($data->start_date))}}</td>
                                <td>{{$data->expire_date != null ? date('d-m-Y', strtotime($data->expire_date)) : __('Lifetime')}}</td>
                                <td>{{$data->renew_status}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Subscription Modal -->
    <div class="modal fade" id="user_add_subscription" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Create Shop')}}</h5>
                    <button type="button" class="close rounded" data-bs-dismiss="modal"><span>×</span></button>
                </div>

                <form action="{{route('landlord.frontend.order.payment.form')}}" id="user_add_subscription_form" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <input type="hidden" name="subs_user_id" id="subs_user_id" value="{{$user->id}}">
                        <input type="hidden" name="package_id" id="subs_pack_id">
                        <input type="hidden" name="name" id="name" value="{{$auth_user->name}}">
                        <input type="hidden" name="email" id="email" value="{{$auth_user->email}}">
                        <input type="hidden" name="payment_gateway" value="manual_payment" class="payment_gateway_passing_clicking_name">

                        <div class="form-group">
                            <label for="subdomain">{{__('Subdomain')}}</label>
                            <select class="form-select subdomain" id="subdomain" name="subdomain">
                                <option value="" selected disabled>{{__('Select a subdomain')}}</option>
                                    @foreach($user->tenant_details ?? [] as $tenant)
                                        <option value="{{$tenant->id}}" {{$tenant->payment_log->package->type == \App\Enums\PricePlanTypEnums::LIFETIME ? 'selected' : ''}}>{{optional($tenant->domain)->domain}}</option>
                                    @endforeach
                                <option value="custom_domain__dd">{{__('Add new subdomain')}}</option>;
                            </select>
                        </div>

                        <div class="form-group custom_subdomain_wrapper mt-3">
                            <label for="custom-subdomain">{{__('Add new subdomain')}}</label>
                            <input class="form--control custom_subdomain" id="custom-subdomain" type="text" autocomplete="off" value="{{old('subdomain')}}"
                                   placeholder="{{__('Subdomain')}}" style="border:0;border-bottom: 1px solid #595959;width: 100%">
                            <div id="subdomain-wrap"></div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="">{{__('Select A Package')}}</label>
                            <select class="form-control package_id_selector" name="package">
                                <option value="">{{__('Select Package')}}</option>
                                @foreach(\App\Models\PricePlan::all() as $price)
                                    <option value="{{$price->id}}" data-id="{{$price->id}}" data-title="{{$price->title}}">
                                        {{$price->title}} {{ '('.amount_with_currency_symbol($price->price).')' }} - {{\App\Enums\PricePlanTypEnums::getText($price->type)}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3" style="display: none">
                            @php
                                $themes = getAllThemeSlug();
                            @endphp
                            <label for="custom-theme">{{__('Add Theme')}}</label>
                            <select class="form-select text-capitalize" name="theme_slug" id="custom-theme">
                                @foreach($themes as $theme)
                                    <option value="{{$theme}}">{{$theme}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            {!! \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm() !!}
                        </div>

                        <div class="form-group single-input d-none manual_transaction_id mt-4">
                            @if(!empty($payment_gateways))
                                <p class="alert alert-info ">{{json_decode($payment_gateways->credentials)->description ?? ''}}</p>
                            @endif

                            <input type="text" name="trasaction_id"
                                   class="form-control form--control mt-2"
                                   placeholder="{{__('Transaction ID')}}">

                            <input type="file" name="trasaction_attachment"
                                   class="form-control form--control mt-2"
                                   placeholder="{{__('Transaction Attachment')}}" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                        <button type="button" class="btn btn-primary" data-bs-target="#final_result">{{__('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="final_result" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__('Confirm Details')}}</h5>
                    <button type="button" class="close rounded" data-bs-dismiss="modal"><span>×</span></button>
                </div>

                    <div class="modal-body">
                        <div class="confirm-details text-center">
                            <div class="confirm-details--icon"><i class="las la-check"></i></div>
                            <h4 class="confirm-details--title mt-3">{{__('New Purchase')}}</h4>

                            <div class="row">
                                <div class="col-6">
                                    <p class="confirm-details--para mt-3">
                                        <span>Shop Name:</span>
                                        <span class="shop_name">Null</span>
                                    </p>
                                    <p class="confirm-details--para mt-3">
                                        <span>Package Name:</span>
                                        <span class="package_name">Null</span>
                                    </p>
                                    <p class="confirm-details--para mt-3">
                                        <span>Theme:</span>
                                        <span class="theme"></span>
                                    </p>
                                </div>

                                <div class="col-6">
                                    <p class="confirm-details--para mt-3">
                                        <span>Price:</span>
                                        <span class="price"></span>
                                    </p>
                                    <p class="confirm-details--para mt-3">
                                        <span>Validity:</span>
                                        <span class="validity"></span>
                                    </p>
                                    <p class="confirm-details--para mt-3">
                                        <span>Payment Gateway:</span>
                                        <span class="payment_gateway"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                        <button type="button" class="btn btn-primary" id="final-submit">{{__('Submit')}}</button>
                    </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <x-custom-js.landloard-unique-subdomain-check :name="'custom_subdomain'"/>

    <script>
        const final_detail = {};

        $(document).on('change','.package_id_selector',function (){
            let el = $(this);
            let form = $('.user_add_subscription_form');
            $('#subs_pack_id').val(el.val());
        });

        let custom_subdomain_wrapper = $('.custom_subdomain_wrapper');
        custom_subdomain_wrapper.hide();
        $(document).on('change', '#subdomain', function (e){
            let el = $(this);
            let subdomain_type = el.val();

            if(subdomain_type === 'custom_domain__dd')
            {
                custom_subdomain_wrapper.slideDown();
                custom_subdomain_wrapper.find('#custom-subdomain').attr('name', 'custom_subdomain');
                final_detail.subdomain = undefined;
            } else {
                custom_subdomain_wrapper.slideUp();
                custom_subdomain_wrapper.removeAttr('#custom-subdomain').attr('name', 'custom_subdomain');
                final_detail.subdomain = $('#subdomain').val();
                final_detail.renew_status = true;
            }
        });

        $(document).on('change','#custom-subdomain',function () {
            final_detail.subdomain = $(this).val();
            final_detail.renew_status = false;
        });

        $(document).on('change', '#subdomain', function (){
            let el = $(this).parent().parent().find(".form-group #custom-theme");
            let subdomain = $(this).val();

                $.ajax({
                    url: '{{route('landlord.admin.tenant.check.subdomain.theme')}}',
                    type: 'POST',
                    data: {
                        _token : '{{csrf_token()}}',
                        subdomain : subdomain
                    },
                    beforeSend: function () {
                        el.find('option').attr('selected', false);
                    },
                    success: function (res) {
                        if(res.theme_slug !== '')
                        {
                            el.find(`option[value="${res.theme_slug}"]`).attr('selected', true);
                        }

                        let custom_theme_wrapper = $('#custom-theme').parent();
                        custom_theme_wrapper.hide();
                        if (res.new_tenant)
                        {
                            custom_theme_wrapper.show();
                        }
                    }
                });
        });

        $(document).on('change', '#custom-theme', function () {
            theme_selected_first = true;
            final_detail.theme = $(this).val();
        });

        $(document).on('submit', '#user_add_subscription_form', function () {
            $(this).find('button[type=submit]').attr('disabled', true);
        });

        const customFormParent = $('.payment_gateway_extra_field_information_wrap');
        customFormParent.children().hide();

        $(document).on('click', '.payment_getway_image ul li', function () {
            let gateway = $(this).data('gateway');
            let manual_transaction_div = $('.manual_transaction_id');

            customFormParent.children().hide();
            if (gateway === 'manual_payment') {
                manual_transaction_div.removeClass('d-none');
            } else {
                manual_transaction_div.addClass('d-none');

                let wrapper = customFormParent.find('#'+gateway+'-parent-wrapper');
                if (wrapper.length > 0)
                {
                    wrapper.fadeIn();
                }
            }

            let gateway_name = $(this).data('gateway');
            $(this).addClass('selected').siblings().removeClass('selected');
            $('.payment-gateway-wrapper').find(('input')).val(gateway_name);
            $('.payment_gateway_passing_clicking_name').val(gateway_name);
            final_detail.payment_gateway = gateway;
        });

        $(document).on('change', 'select[name="package"]', function () {
            let el = $(this);
            let package_id = el.val();
            let package_name = el.find(':selected').text().trim();

            $.ajax({
                url: '{{route('landlord.frontend.package.check')}}',
                type: 'POST',
                data: {
                    _token : '{{csrf_token()}}',
                    package_id : package_id
                },
                success: function (data) {
                    let payment_gateway_wrapper = $('.payment-gateway-wrapper');
                    let selected_payment_gateway = $('input[name="selected_payment_gateway"]');
                    let manual_transaction_id = $('.manual_transaction_id');

                    if(data.price <= 0)
                    {
                        payment_gateway_wrapper.hide();
                        if(selected_payment_gateway.val() === 'manual_payment')
                        {
                            manual_transaction_id.addClass('d-none');
                        }
                    } else {
                        payment_gateway_wrapper.slideDown();
                        if(selected_payment_gateway.val() === 'manual_payment')
                        {
                            manual_transaction_id.removeClass('d-none');
                        }
                    }

                    $('#custom-theme').html(data.theme_list);
                    final_detail.package_id = package_id;
                    final_detail.package_name = package_name;
                    final_detail.price = data.price;
                    final_detail.validity = data.validity;
                    final_detail.payment_gateway = selected_payment_gateway.val();
                }
            });
        });

        const modal_id = '#final_result';
        $(document).on('click' ,'button[data-bs-target="'+modal_id+'"]', function () {
            if (final_detail.subdomain !== undefined && final_detail.package_id !== undefined)
            {
                if (final_detail.price > 0 && final_detail.payment_gateway === undefined)
                {
                    toastr.error(`{{__('Please provide all the required information in the provided fields.')}}`);
                    return;
                }

                if (!final_detail.renew_status && final_detail.theme === undefined)
                {
                    toastr.error(`{{__('Please provide all the required information in the provided fields.')}}`);
                    return;
                }
            } else {
                toastr.error(`{{__('Please provide all the required information in the provided fields.')}}`);
                return;
            }



            const modal = $(modal_id).find('.modal-body');
            $('#user_add_subscription').modal('hide');
            $(modal_id).modal('show');

            console.log(final_detail)
            modal.find('.confirm-details--title').text(final_detail.renew_status ? `{{__('Renew Plan')}}` : `{{__('New Purchase')}}`);
            modal.find('.shop_name').text(final_detail.subdomain);
            modal.find('.package_name').text(final_detail.package_name);
            modal.find('.theme').text(final_detail.theme);
            modal.find('.price').text(final_detail.price);
            modal.find('.validity').text(final_detail.validity);
            modal.find('.payment_gateway').text(final_detail.payment_gateway.replace('_',' '));
        });

        $(document).on('click', '#final-submit', () => {
            $('#user_add_subscription_form').submit();
        });
    </script>
@endsection
