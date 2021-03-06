define(
[
	'emberjs',
	'Library/jquery-with-dependencies',
	'Shared/ResourceCache',
	'text!./DeleteNodeDialog.html'
],
function(Ember, $, ResourceCache, template) {
	return Ember.View.extend({
		template: Ember.Handlebars.compile(template),
		classNames: ['neos-overlay-component'],
		title: '',
		numberOfChildren: 0,
		deleteNode: Ember.K,

		createElement: function() {
			this._super();
			this.$().appendTo($('#neos-application'));
		},

		cancel: function() {
			this.destroyElement();
			this.set('deleteNode', Ember.K);
		}
	}).create();
});