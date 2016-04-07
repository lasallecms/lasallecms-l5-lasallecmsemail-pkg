@extends('lasallecmsadmin::'.$admin_template_name.'.layouts.default')

@section('content')

        <!-- Main content -->
<section class="content">

    <div class="container">


        {{-- form's title --}}
        <div class="row">
            <br /><br />
            {!! $HTMLHelper::adminPageTitle($package_title, 'Email Messages', '') !!}
            <br /><br />
        </div>


        <div class="row">

            @include('lasallecmsadmin::'.$admin_template_name.'.partials.message')

            <div class="col-md-1"></div>

            <div class="col-md-11">

                @if (count($records) > 0 )

                    {!! $HTMLHelper::adminCreateButton($resource_route_name, $model_class, 'right') !!}


                    <form method="POST" action="{{{ Config::get('app.url') }}}/index.php/admin/{!! $resource_route_name !!}/confirmDeletionMultipleRows" accept-charset="UTF-8">
                        {{{ csrf_field() }}}

                        {{-- bootstrap table tutorial http://twitterbootstrap.org/twitter-bootstrap-table-example-tutorial --}}
                        {{-- http://datatables.net/manual/options --}}

                        {{-- the table! --}}
                        <table id="table_id" class="table table-striped table-bordered table-hover" data-order='[[ 1, "desc" ]]' data-page-length='100'>

                            <thead>
                            <tr class="info">

                                {{-- The checkbox --> the primary ID field is always the first field. --}}
                                <th style="text-align: center;"></th>

                                <th style="text-align: center;">From</th>
                                <th style="text-align: center;">To</th>
                                <th style="text-align: center;">Subject</th>
                                <th style="text-align: center;">Sent</th>
                                <th style="text-align: center;">Read</th>
                                <th style="text-align: center;">Date</th>

                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                            </tr>
                            </thead>


                            <tbody>

                                @foreach ($records as $record)

                                    <tr>
                                        <td align="center"><input name="checkbox[]" type="checkbox" value="{!! $record->id !!}"></td>

                                        <td align="left">
                                            @if ($record->from_name)
                                                {!! $record->from_name !!}
                                                <br />
                                                ({!! $record->from_email_address !!})
                                            @else
                                                {!! $record->from_email_address !!}
                                            @endif
                                        </td>

                                        <td align="left">
                                            @if ($record->to_name)
                                                {!! $record->to_name !!}
                                                <br />
                                                ({!! $record->to_email_address !!})
                                            @else
                                                {!! $record->to_email_address !!}
                                            @endif
                                        </td>

                                        <td align="left">{!! $record->subject !!}</td>
                                        <td align="center">{!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->sent) !!}</td>
                                        <td align="center">{!! $HTMLHelper::convertToCheckOrXBootstrapButtons($record->read) !!}</td>
                                        <td align="center">{!! $DatesHelper::convertDateONLYtoFormattedDateString(substr($record->sent_timestamp,0,10)) !!}</td>

                                        {{-- SHOW BUTTON --}}
                                        <td align="center">
                                            <a href="{{{ URL::route('admin.'.$resource_route_name.'.show', $record->id) }}}" class="btn btn-success  btn-xs" role="button">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>

                                        {{-- EDIT BUTTON --}}
                                        <td align="center">
                                                <a href="{{{ URL::route('admin.'.$resource_route_name.'.edit', $record->id) }}}" class="btn btn-success  btn-xs" role="button">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                        </td>

                                        {{-- DELETE BUTTON --}}
                                        <td align="center">
                                            <form method="POST" action="{{{ Config::get('app.url') }}}/index.php/admin/{{ $resource_route_name }}/confirmDeletion/{!! $record->id !!}" accept-charset="UTF-8">
                                                {{{ csrf_field() }}}

                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    <i class="fa fa-times"></i>
                                                </button>

                                            </form>
                                        </td>
                                    </tr>

                                @endforeach

                            </tbody>




                        </table>

                        <br /><br />
                        <button type="submit" class="btn btn-danger" name="deleteMultipleRecords" type="submit">
                            <i class="fa fa-times icon-2x"></i> Delete the checked rows
                        </button>



                        <br /><br /><br />
                        <button class="btn btn-warning text-center"><strong>Please note that deleting an email does not delete its attachments. Future feature!</strong></button>

                        @else
                            <br /><br />
                            <h2>
                                There are no {!! strtolower($HTMLHelper::properPlural($table_name)) !!}. Go ahead, create your first {!! strtolower($HTMLHelper::properPlural($model_class)) !!}!
                            </h2>

                            <br />
                            {!! $HTMLHelper::adminCreateButton($resource_route_name, $model_class, 'left') !!}
                @endif




            </div> <!-- col-md-11 -->

        </div> <!-- row -->




    </div> <!-- container -->

</section>
@stop