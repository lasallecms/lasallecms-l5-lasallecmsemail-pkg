@extends('lasallecmsadmin::'.$admin_template_name.'.layouts.default')

@section('content')

        <!-- Main content -->
<section class="content">

    <div class="container">


        {{-- form's title --}}
        <div class="row">
            <br /><br />
            {!! $HTMLHelper::adminPageTitle($package_title, (ucwords('Email Messages')), '') !!}

            <h1 class="text-center"><span class="label label-info">Show the Email: "{{{ $record->subject }}}"</span></h1>

            <br /><br />
        </div>


        <div class="row">

            @include('lasallecmsadmin::'.$admin_template_name.'.partials.message')

            <div class="col-md-3"></div>

            <div class="col-md-9">

                {{-- the table! --}}
                <table class="table table-striped table-bordered table-condensed table-hover">

                    <tr>
                        <td>
                            ID:
                        </td>
                        <td>
                            {{{ $record->id }}}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            From:
                        </td>
                        <td>
                            @if ($record->from_name)
                                {{{ $record->from_name }}} ({{{ $record->from_email_address }}})
                            @else
                                {{{ $record->from_email_address }}}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td>
                            To:
                        </td>
                        <td>
                            @if ($record->to_name)
                                {{{ $record->to_name }}} ({{{ $record->to_email_address }}})
                            @else
                                {{{ $record->to_email_address }}}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Subject
                        </td>
                        <td>
                            {{{ $record->subject }}}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Message:
                        </td>
                        <td>
                            {{-- UNESCAPED SO THAT THE HTML RENDERS --}}
                            {!! $record->body !!}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Priority:
                        </td>
                        <td>
                            {!! $HTMLHelper::getTitleById('lookup_todo_priority_types', $record->priority_id) !!}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Sent:
                        </td>
                        <td>
                            @if ($record->sent)
                                {!! $DatesHelper::convertDateONLYtoFormattedDateString(substr($record->sent_timestamp,0,10)) !!}
                            @else
                                {!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->sent) !!}
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Read:
                        </td>
                        <td>
                            {!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->read) !!}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Archived:
                        </td>
                        <td>
                            {!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->archived) !!}
                        </td>
                    </tr>


                    <tr>
                        <td>
                            Created At:
                        </td>
                        <td>
                            {!! $DatesHelper::convertDatetoFormattedDateString($record->created_at) !!}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Updated At:
                        </td>
                        <td>
                            {!! $DatesHelper::convertDatetoFormattedDateString($record->updated_at) !!}
                        </td>
                    </tr>

                </table>

            </div> <!-- col-md-9 -->

        </div> <!-- row -->



        <div class="row">

            <br /><br />

            <div class="col-md-5"></div>

            <div class="col-md-3">

                @if (count($records_attachments) > 0)

                    @foreach ($records_attachments as $record_attachment)

                        <hr>

                        @if (
                            (substr($record_attachment->attachment_filename,strlen($record_attachment->attachment_filename) -3,3) == "gif") ||
                            (substr($record_attachment->attachment_filename,strlen($record_attachment->attachment_filename) -3,3) == "png") ||
                            (substr($record_attachment->attachment_filename,strlen($record_attachment->attachment_filename) -3,3) == "jpg")
                        )
                            <img src="{{{ Config::get('app.url') }}}/{{{ $record_attachment->attachment_path }}}/{{{ $record_attachment->attachment_filename }}}" width="150" height="auto" />


                            {{{ Config::get('app.url') }}}/{{{ Config::get('lasallecmsemail.attachment_path') }}}/{{{ $record_attachment->attachment_filename }}}

                            <br />

                            ({{{ $record_attachment->comments }}})

                        @endif

                    @endforeach
                @else
                    This email has no attachments.
                @endif

            </div> <!-- col-md-9 -->

        </div> <!-- row -->



        <div class="row">

            <br /><br />

            <div class="col-md-5"></div>

            <div class="col-md-3">

                @if (!$record->sent)
                    <a href="{{{ URL::route('admin.'.$resource_route_name.'.edit', $record->id) }}}" class="btn btn-success  btn-lg" role="button">
                        <i class="fa fa-edit"></i>  Edit this email message
                    </a>

                    <br /><br />
                @endif

                <a href="{{{ URL::route('admin.'.$resource_route_name.'.index') }}}" class="btn btn-success  btn-lg" role="button">
                    <i class="fa fa-list"></i>  Return to the email messages listing
                </a>


            </div>

            <div class="col-md-7"></div>

        </div>




    </div> <!-- container -->

</section>
@stop