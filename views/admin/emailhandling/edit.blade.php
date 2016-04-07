@extends('lasallecmsadmin::'.$admin_template_name.'.layouts.default')

@section('content')

    <section class="content">

        <div class="container">

            {{-- form's title --}}
            <div class="row">
                <br /><br />
                {!! $HTMLHelper::adminPageTitle($package_title, 'Email Messages', '') !!}
                <h1 class=" text-center"><span class="label label-info">Edit an Email Message</span></h1>
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

                    {!! Form::model($record,
                        [
                        'route' => ['admin.'.$resource_route_name.'.update', $record->id],
                        'method' => 'PUT',
                        //'files' => true,
                         ]
                     ) !!}


                    {{-- the table! --}}
                    <table class="table table-striped table-bordered table-condensed table-hover">

                        <tr>
                            <td>
                                <label for="from_name">Sender's Name: </label>
                            </td>
                            <td>
                                {{{ $record->from_name }}}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>
                                <label for="from_email_address">Sender's Email Address: </label>
                            </td>
                            <td>
                                {{{ $record->from_email_address }}}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        @if ($record->sent)
                            <tr>
                        @else
                            <tr class="warning">
                        @endif
                            <td>
                                <label for="to_name">Recipient's Name:</label>
                            </td>
                            <td>
                                @if ($record->sent)
                                    {{{ $record->to_name }}}
                                @else
                                    {!! Form::text('to_name', Input::old('to_name', $record->to_name), ['size' => $admin_size_input_text_box] ) !!} *
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        @if ($record->sent)
                            <tr>
                        @else
                            <tr class="warning">
                        @endif
                            <td>
                                <label for="to_email_address">Recipient's Email Address: </label>
                            </td>
                            <td>
                                @if ($record->sent)
                                    {{{ $record->to_email_address }}}
                                @else
                                    {!! Form::email('to_email_address', Input::old('to_email_address', $record->to_email_address), ['required' => 'required', 'size' => $admin_size_input_text_box] ) !!} *
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        @if ($record->sent)
                            <tr>
                        @else
                           <tr class="warning">
                        @endif
                            <td>
                                <label for="subject">Subject: </label>
                            </td>
                            <td>
                                @if ($record->sent)
                                    {{{ $record->subject }}}
                                @else
                                    {!! Form::text('subject', Input::old('subject', $record->subject), ['placeholder' => 'Enter a brief subject (no HTML)', 'required' => 'required', 'size' => $admin_size_input_text_box] ) !!} *


                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        @if ($record->sent)
                            <tr>
                        @else
                            <tr class="warning">
                        @endif
                            <td>
                                <label for="body">Body: </label>
                            </td>
                            <td>
                                @if ($record->sent)
                                    {{{ $record->body }}}
                                @else
                                    {!! Form::textarea('body', Input::old('body', $record->body), ['columns' => 8, 'placeholder' => 'Enter a message (no HTML)', 'required' => 'required'] ) !!} *
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr class="warning">
                            <td>
                                <label for="priority_id">Priority: </label>
                            </td>
                            <td>
                                {!! $repository->determineSelectFormFieldToRenderFromRelatedTable($priorityIdField, 'update', $record->id) !!}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>
                                <label for="sent">Sent: </label>
                            </td>
                            <td>
                                @if ($record->sent)
                                    on {!! $DatesHelper::convertDateONLYtoFormattedDateString(substr($record->sent_timestamp,0,10)) !!}
                                @else
                                    {!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->sent) !!}
                                @endif
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>
                                <label for="read">Read: </label>
                            </td>
                            <td>
                                {!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->read) !!}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr class="warning">
                            <td>
                                <label for="archived">Archived: </label>
                            </td>
                            <td>
                                {!! Form::checkbox('archived', '1', Input::old('archived',  $record->archived)) !!}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>
                                <label for="created_at">Created At: </label>
                            </td>
                            <td>
                                {!! $DatesHelper::convertDatetoFormattedDateString($record->created_at) !!}
                            </td>
                        </tr>

                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>
                                <label for="updated_at">Updated At: </label>
                            </td>
                            <td>
                                {!! $DatesHelper::convertDatetoFormattedDateString($record->updated_at) !!}
                            </td>
                        </tr>



                        <tr><td colspan="2"></td></tr>

                        <tr>
                            <td>

                            </td>
                            <td>
                                {{-- Hidden fields --}}
                                <input name="id"      type="hidden" value="{{{ $record->id }}}">
                                <input name="user_id" type="hidden" value="{{{ $record->user_id }}}">


                                {{-- Submit and cancel buttons --}}
                                {!! Form::submit( 'Save Only' ) !!}

                                @if (!$record->sent)
                                    {!! Form::submit( 'Save & Send', ['name' => 'send_email'] ) !!}
                                @endif

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
