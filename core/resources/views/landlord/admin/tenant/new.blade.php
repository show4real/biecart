@php
    $route_name ='landlord';
@endphp
@extends($route_name.'.admin.admin-master')
    @section('title') {{__('Add New User')}} @endsection
@section('style')
    <x-media-upload.css/>
@endsection
@section('content')
    <div class="col-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <x-admin.header-wrapper>
                <x-slot name="left">
                <h4 class="card-title mb-4">{{__('Add New User')}}</h4>
                </x-slot>

                <x-slot name="right">
                    <a href="{{route('landlord.admin.tenant')}}" class="btn btn-info btn-sm">{{__('All Tenants')}}</a>
                </x-slot>

                </x-admin.header-wrapper>
                <x-error-msg/>
                <x-flash-msg/>

                <form class="forms-sample"  action="{{route('landlord.admin.tenant.new')}}" method="post">
                    @csrf
                    <x-fields.input type="text" name="name" class="form-control" placeholder="{{__('name')}}" label="{{__('Name')}}" value="{{old('name')}}"/>
                    <x-fields.input type="text" name="username" class="form-control" placeholder="{{__('username')}}" label="{{__('Username')}}" value="{{old('username')}}"/>
                    <x-fields.input type="email" name="email" class="form-control" placeholder="{{__('email')}}" label="{{__('Email')}}" value="{{old('email')}}"/>
                    <x-fields.input type="text" name="mobile" class="form-control" placeholder="{{__('mobile')}}" label="{{__('Mobile')}}" value="{{old('mobile')}}"/>

                    <x-fields.country-select name="country" label="{{__('Country')}}" value="{{old('country')}}"/>
                    <x-fields.input type="text" name="city" class="form-control" placeholder="{{__('city')}}" label="{{__('City')}}" value="{{old('city')}}"/>
                    <x-fields.input type="text" name="state" class="form-control" placeholder="{{__('state')}}" label="{{__('State')}}" value="{{old('state')}}"/>
                    <x-fields.input type="text" name="company" class="form-control" placeholder="{{__('company')}}" label="{{__('Company')}}" value="{{old('company')}}"/>
                    <x-fields.input type="text" name="address" class="form-control" placeholder="{{__('address')}}" label="{{__('Address')}}" value="{{old('address')}}"/>

                    <x-fields.input type="password" name="password" class="form-control"  label="{{__('Password')}}"/>
                    <x-fields.input type="password" name="password_confirmation" class="form-control"  label="{{__('Confirm Password')}}"/>

                    <button type="submit" class="btn btn-gradient-primary me-2 mt-5">{{__('Submit')}}</button>

                </form>
            </div>
        </div>
    </div>

    <x-media-upload.markup/>
@endsection

@section('scripts')
    <script>

        $(document).ready(function (){

            function removeTags(str) {
                if ((str===null) || (str==='')){
                    return false;
                }
                str = str.toString();
                return str.replace( /(<([^>]+)>)/ig, '');
            }
            $(document).on('keyup paste change','input[name="subdomain"]',function (e){

                let value = removeTags($(this).val()).toLowerCase().replace(/\s/g, "-");
                $(this).val(value)
                if(value.length < 1) {
                    return;
                }
                let msgWrap = $('#subdomain-wrap');
                msgWrap.html('');
                msgWrap.append('<span class="text-warning">{{__('availability checking..')}}</span>');
                axios({
                    url : "{{route('landlord.subdomain.check')}}",
                    method : 'post',
                    responseType: 'json',
                    data : {
                        subdomain: value
                    }
                }).then(function(res){
                    msgWrap.html('');
                    msgWrap.append('<span class="text-success"> '+ value+ ' {{__('is available')}}</span>');
                    $('#login_button').attr('disabled',false)
                }).catch(function (error){
                    var responseData = error.response.data.errors;
                    msgWrap.html('');
                    msgWrap.append('<span class="text-danger"> '+ responseData.subdomain+ '</span>');
                    $('#login_button').attr('disabled',true)
                });

            }); //subdomain check
        }); // end document ready

    </script>
    <x-media-upload.js/>
@endsection

