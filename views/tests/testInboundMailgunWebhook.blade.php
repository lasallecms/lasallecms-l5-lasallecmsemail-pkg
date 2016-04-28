@extends('layouts.default')

@section('content')



    <section class="content">

        <div class="container">

            {{-- form's title --}}
            <div class="row">
                <br /><br />
                {!! $HTMLHelper::adminPageTitle($package_title, 'Email Messages', '') !!}
                {!! $HTMLHelper::adminPageSubTitle(null, 'an Email Message TEST!') !!}
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
                    {!! Form::open([
                    //'route' => 'inboundEmailStandardHandling',
                    'route' => 'inboundEmailCustomHandling',
                    'files' => true,
                    ]) !!}

                    <tr><td colspan="2"></td></tr>

                    <tr class="success">
                        <td>

                        </td>
                        <td>
                            {{-- Hidden fields --}}

                            <input name="email" type="hidden" value="krugerbloom@rogers.com">

                            <input name="body-plain" type="hidden" value="comments There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the end. comments -- ------------------------------------------------------------------------------------ Bob Bloom Create your own custom web app with my FOSS LaSalle Software, based on the wonderful Laravel Framework. SouthLaSalle.com krugerbloom@gmail.com Email Disclaimer: This email communication is confidential. If you are not the intended recipient, please notify me by return email and delete this communication and any copy.">

                            <input name="from" type="hidden" value="Bob Bloom">
                            <input name="sender" type="hidden" value="krugerbloom@rogers.com">

                            <input name="message-headers" type="hidden" value="X-Mailgun-Incoming and a lot of gobble-dee-gook shit">

                            <input name="recipient" type="hidden" value="custom@emailtx.retroradioes.com">

                            <input name="signature" type="hidden" value="c02b9193c1044ccea7085230e6ba8f5357b288735d5b868e8a079791c6f4e1af">

                            <input name="subject" type="hidden" value="1,654321">

                            <input name="timestamp" type="hidden" value="1459459430">

                            <input name="token" type="hidden" value="0a080d77b986a00487f7a41c3647182f3d5f7c9bce21824340">

                            <input name="attachment-count" type="hidden" value="1">


                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <td>
                            FILE UPLOAD!!
                        </td>
                        <td>
                            <input id="attachment-1" name="attachment-1" type="file">
                        </td>
                    </tr>

                    <tr><td colspan="2"><hr></td></tr>

                    <tr>
                        <td>
                            {{-- Submit and cancel buttons --}}
                            {!! Form::submit( 'TEST!' ) !!}

                            {{-- $HTMLHelper::back_button('Cancel') --}}
                            <a href="{{{ URL::route('admin.'.$resource_route_name.'.index') }}}" class="btn btn-default  btn-xs" role="button"><i class="fa fa-times"></i> Cancel</a>
                        </td>
                    </tr>

                    </table>



                    {!! Form::close() !!}







                </div> <!-- container -->


                <hr>

                <br /><br /><br /><br /><br /><br /><br /><br />
@stop