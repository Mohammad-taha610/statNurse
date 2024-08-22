@extends('master')
@section('site-container')
<style>
    [v-cloak] {
        display: none;
    }
</style>

<div class="wrapper wrapper-content animated fadeInRight" id="log-list-page" v-cloak>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="alert alert-danger" v-show="errorMessage">
                        {{ errorMessage }}
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <h4>Filters</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filter-search">Search:</label>
                            <input type="text" class="form-control" id="filter-search" v-model="filter_search" />
                        </div>
                        <div class="col-md-3">
                            <label for="filter-level">Level:</label>
                            <select class="form-control" id="filter-level" v-model="filter_level">
                                <option disabled selected value="">Please select one</option>
                                <option v-for="level in log_levels" :value="level.value">
                                    {{ level.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive" style="margin-top: 15px">
                        <table class="table table-striped">
                            <thead>
                            <tr class="table_header">
                                <th>Date</th>
                                <th>Level</th>
                                <th>Logger</th>
                                <th>Message</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-show="isLoading == true">
                                <td colspan="6" class="text-center loading-message">
                                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><br>
                                    <p>Loading Logs...</p>
                                </td>
                            </tr>
                            <tr v-show="logs.length == 0 && !isLoading && !errorMessage">
                                <td colspan="6" class="text-center empty-message">
                                    No log messages are available.
                                </td>
                            </tr>
                            <tr v-show="errorMessage">
                                <td colspan="6" class="text-center error-message">
                                    Oops. Something went wrong retrieving the log.
                                </td>
                            </tr>
                            <tr v-for="log in filteredLogResults">
                                <td v-text="log.date"></td>
                                <td v-text="log.level"></td>
                                <td v-text="log.logger"></td>
                                <td v-html="log.message"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
        const app = new Vue({
            el: '#log-list-page',
            computed: {
                filteredLogResults: function () {
                    return this.logs.filter(function (log) {

                        if ( (log.level == this.filter_level || this.filter_level===null) ) {
                            return log
                        }

                    }.bind(this))
                },
            },
            data: {
                logs: [{"date":"","logger":"","level":"","message":""}],
                log_levels: [
                    {"value":"ERROR", "name":"ERROR"},
                    {"value":"INFO", "name":"INFO"},
                    {"value":"DEBUG", "name":"DEBUG"},
                    {"value":"WARNING", "name":"WARNING"},
                    {"value":"NOTICE", "name":"NOTICE"}
                ],
                isLoading: true,
                errorMessage: null,
                filter_level: null,
                filter_search: "",
                search_timeout: null
            },
            methods: {
                getLog: function() {
                    this.isLoading = true
                    modRequest.request('system.log', null, {"search":this.filter_search},
                        function(response) {
                            this.logs = response.logs;
                            this.isLoading = false;
                            this.errorMessage = null;
                        }.bind(this),
                        function(error) {
                            var errorStack = JSON.parse(error.responseText);
                            this.errorMessage = errorStack.error.message;
                            this.isLoading = false;
                        }.bind(this));
                }
            },
            watch: {
                filter_search: function(){
                    //this.getLog();

                    if (this.search_timeout)
                        clearTimeout(this.search_timeout);
                    this.search_timeout = setTimeout( function () {
                        app.getLog()
                    }, 500)
                }
            },
            created: function() {
                this.getLog();
            }
        });

</script>
@show