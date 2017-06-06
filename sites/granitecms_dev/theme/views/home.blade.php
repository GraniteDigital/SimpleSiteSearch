@extends('granitecms_dev.theme.views.layouts.default')

@section('content')
<div id="app">
	
	<form class="form-wrapper cf">
		<button type="submit" v-on:click="search">Search</button>
	  	<input type="text" placeholder="Search here..." required v-model="searchInput" v-on:click="search">
	</form>

	<div v-for="item in sites" class="website">

		<div class="left-image">
			<img v-bind:src="[ item.image ]" />
		</div>
		<div class="right-info">
			<a v-bind:href="[ item.url ]" target="_BLANK"><h2>@{{ item.name }}</h2></a>
			<a v-bind:href="[ item.url ]" target="_BLANK">Link</a>
			<p>
				<strong>Published: </strong> @{{ item.published_date }} <br />
				<strong>Backend Developers: </strong> @{{ item.backend_dev }} <br />
				<strong>Frontend Developers: </strong> @{{ item.frontend_dev }} <br />
				<strong>Project Managers: </strong> @{{ item.project_managers }} <br />

				<strong>Addons: </strong> @{{ item.addons }} <br />
			</p>
		</div>
	</div>


	<div v-if="sites.length === 0">
		<div class="no-sites">
			<h1>No Sites To Display</h1>
		</div>
	</div>
</div>
@stop

