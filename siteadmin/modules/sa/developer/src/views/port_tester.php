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
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="host">Host:</label>
                                <input style="display: inline-block" type="text" class="form-control" id="host" v-model="host" />

                            </div>
                            <button class="btn btn-primary" v-on:click="startTesting">Test</button>

                        </div>
                    </div>
                    <div class="table-responsive" style="margin-top: 15px">
                        <table class="table table-striped">
                            <thead>
                            <tr class="table_header">
                                <th>Port</th>
                                <th>Host</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-show="isLoading == true">
                                <td colspan="6" class="text-center loading-message">
                                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br><br>
                                    <p>Loading Ports...</p>
                                </td>
                            </tr>
                            <tr v-show="ports.length == 0 && !isLoading && !errorMessage">
                                <td colspan="6" class="text-center empty-message">
                                    No ports are available.
                                </td>
                            </tr>
                            <tr v-show="errorMessage">
                                <td colspan="6" class="text-center error-message">
                                    Oops. Something went wrong retrieving the status.
                                </td>
                            </tr>
                            <tr v-for="port in decoratedPorts">
                                <td>{{ port.name }} {{ port.port }}</td>
                                <td v-text="port.host"></td>
                                <td v-bind:style="{ color: port.activeColor }" style="font-size: 20px;" v-html="port.status"></td>
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
            decoratedPorts: function () {
                return this.ports.filter(function (port) {

                    if ( port.status == '0' ) {
                        port.status = '<span class="pending"><i class="fas fa-hourglass-half"></i> Pending</span>';
                        port.activeColor = 'black';
                    }
                    else if ( port.status == '1' ) {
                        port.status = '<span class="open"><i class="fas fa-check-square"></i> Open</span>';
                        port.activeColor = 'green';
                    }
                    else if ( port.status == '2' ) {
                        port.status = '<span class="closed"><i class="fas fa-times-circle"></i> Closed</span>';
                        port.activeColor = 'red';
                    }

                    return port;

                }.bind(this))
            },
        },
        data: {
            ports: [
                {"name":"FTP", "port":"21", "host":"64.191.166.29", "status":"0", "activeColor":null },
                {"name":"SMTP", "port":"25", "host":"smtp.gmail.com", "status":"0", "activeColor":null },
                {"name":"DNS", "port":"53", "host":"8.8.8.8", "status":"0", "activeColor":null },
                {"name":"HTTP", "port":"80", "host":"mail.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"HTTPS", "port":"443", "host":"mail.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"HTTP/ALT", "port":"8080", "host":"secretariat.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"HTTPS/ALT", "port":"8443", "host":"secretariat.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"POP", "port":"110", "host":"mail.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"IMAP", "port":"143", "host":"mail.intelliwire.net", "status":"0", "activeColor":null },
                {"name":"IMAPS", "port":"993", "host":"smtp.gmail.com", "status":"0", "activeColor":null },
                {"name":"SMTP TLS", "port":"587", "host":"smtp.gmail.com", "status":"0", "activeColor":null },
                {"name":"SMTP SSL", "port":"465", "host":"smtp.gmail.com", "status":"0", "activeColor":null },
                {"name":"CPANEL", "port":"2083", "host":"64.191.166.29", "status":"0", "activeColor":null },
                {"name":"CPANEL WEBMAIL", "port":"2096", "host":"64.191.166.29", "status":"0", "activeColor":null },
                {"name":"CPANEL WHM", "port":"2087", "host":"64.191.166.29", "status":"0", "activeColor":null }


            ],
            isLoading: false,
            errorMessage: null,
            host: ''
        },
        methods: {
            updatePortEntry : function(port, status, host) {

                for(var i in this.ports) {
                    if (this.ports[i].port==port) {
                        this.ports[i].status = status;
                        this.ports[i].host = host;
                        break;
                    }
                }
            },
            startTesting: function() {

                for(var i in this.ports) {

                    var host = this.host ? this.host : this.ports[i].host;
                    this.ports[i].status = 0;

                    modRequest.request('developer.ajax_port_test', null, {"host": host, "port": this.ports[i].port },
                        function (response) {
                            this.updatePortEntry(response.port, response.status, response.host );

                            this.isLoading = false;
                            this.errorMessage = null;
                        }.bind(this),
                        function (error) {
                            var errorStack = JSON.parse(error.responseText);
                            this.errorMessage = errorStack.error.message;
                            this.isLoading = false;
                        }.bind(this));

                }

            }
        },
        watch: {

        },
        created: function() {
            this.startTesting();
        }
    });

</script>
@show