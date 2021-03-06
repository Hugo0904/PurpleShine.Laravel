@extends('layouts.index')
@section('content')
    <section class="content-header">
        <h1>Dashboard</h1>
        <ol class="breadcrumb">
            <li><a href="/permission"><i class="fa fa-key"></i>Dashboard</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Dashboard</h3>

                        <div class="box-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" name="table_search" class="form-control pull-right"
                                       placeholder="Search">

                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover">
                            <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Dashboard</th>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>read-agent</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>create-agent</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection