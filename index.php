<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Backbone Stuff</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

</head>
<body>
	<div class="container">
		<h1>User Manager</h1>
		<hr />
		<div class="page"></div>
	</div>

	<!-- Templates -->

	<script type="text/template" id="edit-user-template">

		<form class="edit-user-form">
			<legend><%= user ? 'Update' : 'Create' %> User</legend>
			<label>First Name</label>
			<input type="text" name="firstname" value="<%= user ? user.get('firstname') : '' %>" />
			<label>last Name</label>
			<input type="text" name="lastname" value="<%= user ? user.get('lastname') : '' %>" />
			<label>Age</label>
			<input type="text" name="age" value="<%= user ? user.get('age') : '' %>" />
			<hr />

			<button type="submit" class="btn btn-info"><%= user ? 'Update' : 'Create' %></button>
			<% if(user) { %>
				<input type="hidden" name="id" value="<%= user.id %>" />
				<button class="btn btn-danger delete">Delete</button>
			<% } %>
		</form>

	</script>

	<script type="text/template" id="user-list-template">
		<a href="#/new" class="btn btn-primary">New User</a>
		<hr />
		<table class="table striped">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Age</th>
					<th></th>
				</tr>	
			</thead>
			<tbody>
				<% _.each(users, function(user) { %>

					<tr>
						<td><%= user.get('firstname') %></td>
						<td><%= user.get('lastname') %></td>
						<td><%= user.get('age') %></td>
						<td><a href="#/edit/<%= user.id %>" class="btn">Edit</a></td>
					</tr>

				<% }); %>

			</tbody>
		</table>
	</script>

	<!-- End templates -->

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.0/backbone-min.js"></script>
	<script>

		/*** Snipplets ***/

		//adds a prefix to ajax requests for external servers
		$.ajaxPrefilter(function(options, originalOptions, jqXHR){
			options.url = 'http://backbonejs-beginner.herokuapp.com' + options.url;
		});

		//serializes a form's values
		$.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			$.each(a, function() {
				if(o[this.name] !== undefined){
					if(!o[this.name].push){
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				}else{
					o[this.name] = this.value || '';
				}
			});
			return o;
		};

		/*** End Snipplets ***/

		/*** Models ***/

		//used for single entities
		var User = Backbone.Model.extend({
			urlRoot: '/users'
		});

		/*** End Models ***/



		/*** Collections ***/

		//used for arrays of entities (note the Users [collection] vs the User [Model])
		var Users = Backbone.Collection.extend({
			url: '/users'
		});

		/*** End Collections ***/

		/*** Views ***/

		var UserList = Backbone.View.extend({
			el: '.page',
			render: function(){
				var that = this;
				var users = new Users();
				users.fetch({
					success: function(users){
						var template = _.template($('#user-list-template').html(), {users: users.models});
						that.$el.html(template);
					}
				})
			}
		});

		var EditUser = Backbone.View.extend({
			el: '.page',
			render: function(options){
				var that = this;
				if(options.id){
					that.user = new User({id: options.id});
					that.user.fetch({
						success: function(user){
							var template = _.template($('#edit-user-template').html(), {user: user});
							that.$el.html(template);
						}
					})
				}else{
					var template = _.template($('#edit-user-template').html(), {user: null});
					this.$el.html(template);
				}
			},
			events: {
				'submit .edit-user-form': 'saveUser',
				'click .delete': 'deleteUser'
			},
			saveUser: function(ev){
				$(ev.currentTarget);
				var userDetails = $(ev.currentTarget).serializeObject();
				var user = new User();
				user.save(userDetails, {
					success: function(user){
						//alert(user.toJSON())
						//console.log(user);
						router.navigate('', {trigger: true}); //used for saving state of your page
					}
				})
				//console.log(userDetails);
				return false;
			},
			deleteUser: function(ev){
				this.user.destroy({
					success: function () {
						router.navigate('', {trigger: true});
					}
				})

				return false;
			}
		});
		
		/*** End Views ***/


		/*** ROUTES ***/

		var Router = Backbone.Router.extend({
			routes: {
				'': 'home',
				'new': 'editUser',
				'edit/:id': 'editUser'
			}	
		});

		var userList = new UserList();
		var editUser = new EditUser();
		var router   = new Router();

		router.on('route:home', function(){
			userList.render();
		});

		router.on('route:editUser', function(id){
			editUser.render({id: id});
			//console.log("edit user");
		});

		Backbone.history.start();

		/*** End Routes ***/

	</script>
</body>
</html>