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

<h3>Here are your updates:</h3>
@foreach ($alternateSortString1s as $alternateSortString1)
    <a class="btn btn-success" style="color: whitesmoke;"
        href="{{{ $url }}}/index.php/customercare/displayorderupdates/{{{ $alternateSortString1->alternate_sort_string1 }}}">
        order #{{{ $alternateSortString1->alternate_sort_string1 }}}
    </a>
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