<!DOCTYPE>
<html lang="en" ng-app='supervisorApp'>
<head>
<meta charset="utf-8">
<title>Supervisor Manager</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
<style type="text/css">
/* Move down content because we have a fixed navbar that is 50px tall */
.main {
	padding-top:30px;
}
.navbar-brand {
	float:none;
}
@media (min-width: 768px) {
	.main {
		padding-right: 40px;
		padding-left: 40px;
	}
	.sidebar {
		position: fixed;
		top: 0;
		bottom: 0;
		left: 0;
		z-index: 1000;
		display: block;
		padding: 20px;
		overflow-x: hidden;
		overflow-y: auto;
		background-color: #F5F5F5;
		border-right: 1px solid #EEE;
	}
}

.main .page-header {
	margin-top: 0;
}
</style>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular.min.js"></script>
<script>
var supervisorApp = angular.module('supervisorApp', [])

supervisorApp.controller('SupervisorListCtrl', function($scope, $http) {
	$http.get('allStatus.php').success(function(data) {
		$scope.supervisors = data
		$scope.processes = []
		for (s in data) {
			data[s].shortname = data[s].alias.slice(0, data[s].alias.indexOf("."))
			data[s].groups = {}
			data[s].states = {
				'STOPPED'  : 0, // (0) - The process has been stopped due to a stop request or has never been started.
				'STARTING' : 0, // (10) - The process is starting due to a start request.
				'RUNNING'  : 0, // (20) - The process is running.
				'BACKOFF'  : 0, // (30) - The process entered the STARTING state but subsequently exited too quickly to move to the RUNNING state.
				'STOPPING' : 0, // (40) - The process is stopping due to a stop request.
				'EXITED'   : 0, // (100) - The process exited from the RUNNING state (expectedly or unexpectedly).
				'FATAL'    : 0, // (200) - The process could not be started successfully.
				'UNKNOWN'  : 0  // (1000) - The process is in an unknown state (supervisord programming error).
			}
			for (p in data[s].processes) {
				data[s].states[data[s].processes[p].statename]++
				if (data[s].processes[p].group == data[s].processes[p].name) {
					data[s].processes[p].group = ""
					data[s].processes[p].procname = data[s].processes[p].name
					continue
				}

				if (!data[s].groups.hasOwnProperty(data[s].processes[p].group)) {
					data[s].groups[data[s].processes[p].group] = 1
				} else {
					data[s].groups[data[s].processes[p].group]++
				}
				data[s].processes[p].procname = data[s].processes[p].group+":"+ data[s].processes[p].name
			}
		}
		console.debug(data)
	})
	$scope.glyphFor = function(state) {
		switch (state) {
			case 'STOPPED':  return 'glyphicon-stop'
			case 'STARTING': return 'glyphicon-circle-arrow-up'
			case 'RUNNING':  return 'glyphicon-play'
			case 'BACKOFF':  return 'glyphicon-time'
			case 'STOPPING': return 'glyphicon-circle-arrow-down'
			case 'EXITED':   return 'glyphicon-off'
			case 'FATAL':    return 'glyphicon-warning-sign'
			default:         return 'glyphicon-warning-sign'
		}
	}
	$scope.stateClass = function(state) {
		switch (state) {
			case 'STOPPED':  return 'default'
			case 'STARTING': return 'info'
			case 'RUNNING':  return 'success'
			case 'BACKOFF':  return 'info'
			case 'STOPPING': return 'info'
			case 'EXITED':   return 'warning'
			case 'FATAL':    return 'danger'
			default:         return 'danger'
		}
	}
	$scope.isRestartable = function(status) {
		return status == 'RUNNING'
	}
	$scope.isStartable = function(status) {
		return status != 'RUNNING'
	}
})
</script>
</head>
<body ng-controller="SupervisorListCtrl">

<div class="container-fluid">
	<div class="row">

		<div class="col-sm-3 col-md-2 sidebar">
			<div class="navbar-brand">Coverage Dashboard</div>
			<ul class="nav nav-sidebar">
				<li ng-repeat="supervisor in supervisors">
					<a href="#{{ supervisor.alias }}">
						{{ supervisor.shortname }}
						<span
							ng-repeat="(state, count) in supervisor.states"
							ng-if="count"
							class="pull-right label label-{{ stateClass(state) }}"
							title="{{ state | lowercase }}"
							>{{ count }} <span class="glyphicon {{ glyphFor(state) }}"></span></span>
					</a>
				</li>
			</ul>
		</div>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">Dashboard</h1>
			<div ng-repeat="supervisor in supervisors" id="{{ supervisor.alias }}">
				<h2 class="sub-header">{{ supervisor.alias }}</h2>
				<div class="table-responsive">
					<table class="table table-condensed table-striped">
						<thead>
							<tr>
								<!-- <th>Server</th> -->
								<th width="60%">Name</th>
								<th width="10%">State</th>
								<th width="20%">Description</th>
								<th width="10%">Action</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat="(group, count) in supervisor.groups">
								<th>{{ group }} ({{ count }})</th>
								<td></td>
								<td></td>
								<td>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ group }}&amp;action=startgroup"><span class="glyphicon glyphicon-play" title="Start Group"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ group }}&amp;action=restartgroup"><span class="glyphicon glyphicon-refresh" title="Restart Group"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ group }}&amp;action=stopgroup"><span class="glyphicon glyphicon-stop" title="Stop Group"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;"></span>
									<span class="glyphicon" style="width:1em;"></span>
								</td>
							</tr>
							<tr ng-repeat="process in supervisor.processes">
								<!-- <th>{{ supervisor.alias }}</th> -->
								<td><span ng-if="process.group"><em>{{ process.group }}</em>:</span>{{ process.name }}</td>
								<td><span class="label label-{{ stateClass(process.statename) }}">{{ process.statename | lowercase }}</span></td>
								<td>{{ process.description }}</td>
								<td>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.procname }}&amp;action=start" ng-if="isStartable(process.statename)"><span class="glyphicon {{ glyphFor(process.statename) }}" title="Start"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.procname }}&amp;action=restart" ng-if="isRestartable(process.statename)"><span class="glyphicon glyphicon-refresh" title="Restart"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.procname }}&amp;action=stop" ng-if="isRestartable(process.statename)"><span class="glyphicon {{ glyphFor(process.statename) }}" title="Stop"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.procname }}&amp;action=viewlog"><span class="glyphicon glyphicon-align-left" title="View Log"></span></a>
									</span>
									<span class="glyphicon" style="width:1em;">
										<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.procname }}&amp;action=clearlog"><span class="glyphicon glyphicon-trash" title="Clear Log"></span></a>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

</body>
</html>
