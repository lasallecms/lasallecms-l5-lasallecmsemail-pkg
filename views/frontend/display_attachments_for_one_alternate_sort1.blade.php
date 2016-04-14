<!doctype html>
<html lang="en">

<head>
    @section('meta')
    @include('layouts.header_meta')
    @show


    @section('styles')
    @include('layouts.header_css')
    @show
</head>

<body id="page-top">

<div class="container">

    {{-- Content --}}
    <h1>{{{ $username }}}</h1>

    <h3>Order #{{{ $alternatesortstring1 }}}</h3>

    @if (count($attachments) < 1)
        <br />
        <h3><span class="label label-warning" style="color: whitesmoke;">There are no update pictures for this order. </span></h3>
    @endif

    <?php $current_email_messages_id = 1; ?>
    @foreach ($attachments as $attachment)

        @if ($attachment->email_messages_id != $current_email_messages_id)
            <br /><br />
            {{{ $attachment->comments }}}
            <br /><br />
        @endif

        <?php $current_email_messages_id = $attachment->email_messages_id; ?>

        <img
            src="{{{ $url }}}/{{{ $attachment_path }}}/{{{ $attachment->attachment_filename }}}"
            width="350"
            height="auto"
        />
        <br /><br />
    @endforeach

</div><!-- /.container -->

{{-- Footer --}}
@section('footer')

@show


{{-- Footer JS --}}
@section('footer_scripts')
@include('layouts.footer_scripts')
@show

</body>

</html>