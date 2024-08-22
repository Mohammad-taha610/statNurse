@extends('master')
@section('site-container')
@asset::/siteadmin/developer/css/timers.css

<div class="wrapper wrapper-content animated fadeInRight" id="log-list-page" v-cloak>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>To time a page add ?sa_profile to the end of the url.</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                        	<div style="margin-bottom: 5px">
	                            <label for="filter-level">Instance:</label>
	                            <select class="form-control" id="filter-level" v-model="filter_level">
	                                <option selected value="">Please select one</option>
	                                <option v-for="instance in instances" :value="instance.instance_number">
	                                    {{ instance.url }} {{ instance.instance_number }} {{ roundSingleTime(instance.instance_start, instance.instance_end) }} {{ instance.date }}
	                                </option>
	                            </select>
                        	</div>
                        	<div>
                        		<button v-on:click="getTiming"><i class="fa fa-refresh"></i></button>
                        		<label for="show-fast">Show fast timings:</label>
                            	<input  type="checkbox" id="show-fast" v-model="show_fast_timings" />
                        	</div>
                        </div>
     
                    </div>
                    <div class="table-responsive" style="margin-top: 15px">

                         <div v-for="(instance, index) in filteredResults" class="timing-instance" :key="index">
                             <div class="row timing-header">
                                 <div class="col-md-3">{{ instance.url }}</div>
                                 <div class="col-md-1 text-right">{{ roundSingleTime(instance.instance_start, instance.instance_end) }}</div>
                                 <div class="col-md-1 text-right">Extra</div>
                                 <div class="col-md-7">
                                     <div style="background-color: #4dfddb; height: 20px; width: 100%; display: inline-block">

                                     </div>
                                 </div>
                             </div>
                             <div class="row timing-single" v-for="(timing, index) in instance.timings" v-if="timing.name">
                                 <div class="col-md-3">{{ timing.name }}</div>
                                 <div class="col-md-1 text-right">{{ roundSingleTime(timing.start, timing.end) }}</div>
                                 <div class="col-md-1 text-right">{{ timing.info }}</div>
                                 <div class="col-md-7">
                                     <div style="position: relative">
                                         <div class="timing-bar" :style="{ 'width': calculateWidth(instance, timing), 'left': calculateLeft(instance, timing) }">

                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

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
            filteredResults: function () {


            	var results = [];
        		for(x in this.instances) {

        			instance = this.instances[x];

                    if ( (instance.instance_number == this.filter_level || this.filter_level===null || this.filter_level==='') ) {

        				var filterTimings = {};
        				var copiedObject = {};

						var index = 0;
        				for (key in instance.timings) {
        					timing = instance.timings[key];
					        if ( this.roundSingleTime(timing.start, timing.end) > .09  || this.show_fast_timings ) {
								filterTimings[index] = timing;
								index++;
                			}
					    }

						
						copiedObject.instance_number = instance.instance_number;
						copiedObject.instance_start = instance.instance_start;
						copiedObject.instance_end = instance.instance_end;
					    copiedObject.timings = filterTimings;
					    copiedObject.url =  instance.url;

                        results.push(copiedObject);
                    }

                }

                return results;


            },
        },
        data: {
            instances: [],
            log_levels: [
                {"value":"ERROR", "name":"ERROR"},
                {"value":"INFO", "name":"INFO"},
                {"value":"DEBUG", "name":"DEBUG"},
                {"value":"WARNING", "name":"WARNING"},
                {"value":"NOTICE", "name":"NOTICE"}
            ],
            isLoading: true,
            filter_level: null,
            filter_search: "",
            show_fast_timings: false
        },
        methods: {
            getTiming: function() {
                this.isLoading = true
                modRequest.request('developer.timing', null, {"search":this.filter_search},
                    function(response) {
                        this.instances = response.instances;
                        this.isLoading = false;
                    }.bind(this),
                    function(error) {
                        this.isLoading = false;
                    }.bind(this));
            },
            roundSingleTime: function(start, end) {
                return ((end - start) * 1000).toFixed(2);
            },
            calculateLeft: function(instance, single_time) {
                var totalTime = (instance.instance_end - instance.instance_start);
                var singleTime = single_time.start - instance.instance_start;
                var left = ((singleTime / totalTime) * 100);
                return left+'%';
            },
            calculateWidth: function(instance, single_time) {
                var totalTime = (instance.instance_end - instance.instance_start);
                var singleTime = (single_time.end - single_time.start);
                var percent = (singleTime / totalTime) * 100;
                return percent+'%';
            }
        },
        watch: {

        },
        created: function() {
            this.getTiming();
        }
    });

</script>
@show