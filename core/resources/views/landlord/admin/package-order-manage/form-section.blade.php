@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Order Page Settings')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-error-msg/>
                <x-flash-msg/>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{__('Order Page Settings')}}</h4>
                        <form action="{{route(route_prefix().'admin.package.order.page')}}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mt-3">
                                <label for="order_page_form_title">{{__('Order Form Title')}}</label>
                                <input type="text" name="order_page_form_title"
                                       value="{{get_static_option('order_page_form_title')}}" class="form-control"
                                       id="order_page_form_title">
                            </div>
                            <div class="form-group">
                                <label for="order_page_form_mail">{{__('Select Order Custom Form')}}</label>
                                <select class="form-control" name="order_form">
                                    @foreach($all_custom_form as $form)
                                        <option
                                            value="{{$form->id}}" {{!empty(get_static_option('order_form')) ? 'selected' : ''}}>{{$form->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Settings')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
