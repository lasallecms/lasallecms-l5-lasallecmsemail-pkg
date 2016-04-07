@extends('lasallecmsadmin::'.$admin_template_name.'.layouts.default')

@section('content')

<section class="content">

    <div class="container">

        {{-- form's title --}}
        <div class="row">
            <br /><br />
            {!! $HTMLHelper::adminPageTitle($package_title, 'Email Messages', '') !!}
            {!! $HTMLHelper::adminPageSubTitle(null, 'an Email Message') !!}
            <br /><br />
        </div>



        <div class="row">
            <div class="col-md-3"></div>

            <div class="col-md-9" style="font-weight: bold;font-size: 150%;">
                @include('lasallecmsadmin::'.$admin_template_name.'.partials.errors')
            </div>
        </div>


        <div class="row">
            <div class="col-md-3"></div>

            <div class="col-md-9" style="font-weight: bold;font-size: 150%;">
                @include('lasallecmsadmin::'.$admin_template_name.'.partials.message')
            </div>
        </div>



        <div class="row">

            <div class="col-md-3"></div>

            <div class="col-md-9">
                {!! Form::open(
                    [
                    'route' => 'admin.'.$resource_route_name.'.store',
                    //'files' => true,
                    ]
                ) !!}


                {{-- the table! --}}
                <table class="table table-striped table-bordered table-condensed table-hover">

                    <tr>
                        <td>
                            <label for="from_email_address">From: </label>
                        </td>
                        <td>
                            <input type="text" name="from_name" value="{{{ $user->name }}}" readonly="readonly" size="35" style="background-color:#d1d1d1;" />
                            <br /><br />
                            <input type="email" name="from_email_address" value="{{{ $user->email }}}" readonly="readonly" size="35" style="background-color:#d1d1d1;" />
                        </td>
                    </tr>

                    <tr><td colspan="2"></td></tr>

                    <tr class="warning">
                        <td>
                            <label for="to_name">Recipient's Name:</label>
                        </td>
                        <td>
                            <input type="text" name="to_name" value="{{{ old('to_name') }}}" size="35" placeholder="(optional)" />
                        </td>
                    </tr>

                    <tr><td colspan="2"></td></tr>

                    <tr class="warning">
                        <td>
                            <label for="to_email_address">Recipient's Email Address: </label>
                        </td>
                        <td>
                            <input type="email" name="to_email_address" value="{{{ old('to_email_address') }}}" size="35" required placeholder="Enter a valid email address" /> *
                        </td>
                    </tr>

                    <tr><td colspan="2"></td></tr>

                    <tr class="warning">
                        <td>
                            <label for="subject">Subject: </label>
                        </td>
                        <td>
                            <input type="text" name="subject" value="{{{ old('subject') }}}" size="35" required placeholder="Enter a brief subject" /> *
                        </td>
                    </tr>

                    <tr><td colspan="2"></td></tr>

                    <tr class="warning">
                        <td>
                            <label for="body">Body: </label>
                        </td>
                        <td>
                            <textarea name="body" id="body" required placeholder="Enter a message (no HTML)" columns="9">{{{ old('body') }}}</textarea> *
                        </td>
                    </tr>



                    <tr><td colspan="2"></td></tr>

                    <tr class="success">
                        <td>

                        </td>
                        <td>
                            {{-- Hidden fields --}}
                            <input name="user_id" type="hidden" value="{{{ $user->id }}}">


                            {{-- Submit and cancel buttons --}}
                            {!! Form::submit( 'Save Only' ) !!}
                            {!! Form::submit( 'Save & Send', ['name' => 'send_email'] ) !!}

                            {{-- $HTMLHelper::back_button('Cancel') --}}
                            <a href="{{{ URL::route('admin.'.$resource_route_name.'.index') }}}" class="btn btn-default  btn-xs" role="button"><i class="fa fa-times"></i> Cancel</a>
                        </td>
                    </tr>

                </table>



                {!! Form::close() !!}

                <br />
                <button class="btn btn-warning text-center"><strong>* indicates that the field is required</strong></button>

                <br /><br />
                <button class="btn btn-warning text-center"><strong>Note that there is no attachment handling</strong></button>

            </div> <!-- col-md-9 -->



        </div> <!-- row -->


    </div> <!-- container -->

</section>

@stop