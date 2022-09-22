{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Analytic  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Analytic</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Month
                    </h3>
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="#monthSummary" data-toggle="tab">Summary</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#monthDetail" data-toggle="tab">Detail</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content p-0">
                        <div class="chart tab-pane active" id="monthSummary">
                            <canvas id="canvasSummaryMonth" height="300"></canvas>
                        </div>
                        <div class="chart tab-pane" id="monthDetail">
                            <canvas id="canvasDetailsMonth" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header ui-sortable-handle" style="cursor: move;">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Year
                    </h3>
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="#yearSummary" data-toggle="tab">Summary</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#yearDetail" data-toggle="tab">Detail</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content p-0">
                        <div class="chart tab-pane active" id="yearSummary">
                            <canvas id="canvasSummaryYear" height="300"></canvas>
                        </div>
                        <div class="chart tab-pane" id="yearDetail">
                            <canvas id="canvasDetailsYear" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        var urlSummaryMonth = "{{ url('analytic/chartMonthSummary') }}";
        var urlDetailMonth = "{{ url('analytic/chartMonthDetail') }}";
        var urlSummaryYear = "{{ url('analytic/chartYearSummary') }}";
        var urlDetailYear = "{{ url('analytic/chartYearDetail') }}";
    </script>
    <script src="{{ asset('js/backend/analytic/index.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
