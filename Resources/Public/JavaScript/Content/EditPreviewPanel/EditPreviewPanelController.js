/**
 * Controller for the Edit / Preview Panel
 *
 * Singleton
 */
define(
[
	'emberjs',
	'Library/jquery-with-dependencies',
	'Shared/LocalStorage',
	'Shared/Configuration',
	'Shared/ResourceCache',
	'Shared/Notification',
	'./EditingMode',
	'./PreviewMode'
], function(
	Ember,
	$,
	LocalStorage,
	Configuration,
	ResourceCache,
	Notification,
	EditingMode,
	PreviewMode
) {
	return Ember.Controller.extend({
		editPreviewPanelMode: false,
		currentlyActiveMode: null,
		previousActiveMode: null,

		configuration: null,
		editingModes: [],
		previewModes: [],

		_deactivatePreviouslyActiveMode: function() {
			if (this.get('currentlyActiveMode')) {
				Ember.sendEvent(this.get('currentlyActiveMode'), 'deactivate');
				this.set('currentlyActiveMode.active', false);
				this.set('previousActiveMode', this.get('currentlyActiveMode'));
			}
		}.observesBefore('currentlyActiveMode'),

		_activateCurrentlyActiveMode: function() {
			this.set('currentlyActiveMode.active', true);
			Ember.sendEvent(this.get('currentlyActiveMode'), 'activate');
		}.observes('currentlyActiveMode'),

		isEditingModeActive: function() {
			return this.get('currentlyActiveMode.isEditingMode');
		}.property('currentlyActiveMode'),

		isPreviewModeActive: function() {
			return this.get('currentlyActiveMode.isPreviewMode');
		}.property('currentlyActiveMode'),

		_saveCurrentlyActiveModeInLocalStorage: function() {
			if (!this.get('previousActiveMode')) {
				return;
			}
			var identifier = this.get('currentlyActiveMode.identifier'),
				reloadRequired = false;

			LocalStorage.setItem('editPreviewMode', identifier);
			if (this.get('previousActiveMode') && this.get('previousActiveMode.typoScriptRenderingPath') !== this.get('currentlyActiveMode.typoScriptRenderingPath')) {
				reloadRequired = true;
			}
			if (reloadRequired) {
				require(
					[
						'Content/Application'
					],
					function(ContentModule) {
						ContentModule.showPageLoader();
					}
				);
			}
			TYPO3_Neos_Service_ExtDirect_V1_Controller_UserController.updatePreferences({'contentEditing.editPreviewMode': identifier}, function() {
				if (reloadRequired) {
					window.location.reload(false);
				}
			});
		}.observes('currentlyActiveMode'),

		init: function() {
			var that = this;
			$.when(ResourceCache.getItem(Configuration.get('EditPreviewDataUri'))).done(function(dataString) {
				var data = JSON.parse(dataString);
				that.set('configuration', data);
			}).fail(function(xhr, status, error) {
				Notification.error('Failed loading edit / preview data.');
				console.error('Error loading menu data.', xhr, status, error);
			});

			if (LocalStorage.getItem('editPreviewPanelMode') === true) {
				this.toggleEditPreviewPanelMode();
			}
		},

		toggleEditPreviewPanelMode: function() {
			this.toggleProperty('editPreviewPanelMode');
		},

		_onConfigurationChanged: function() {
			if (!this.get('configuration')) {
				return;
			}
			var editingModes = [],
				previewModes = [],
				editPreviewConfiguration = this.get('configuration'),
				storedActiveViewModeIdentifier = LocalStorage.getItem('editPreviewMode'),
				currentlyActiveMode = null;
			$.each(editPreviewConfiguration, function(identifier, configuration) {
				var modeConfiguration = $.extend({identifier: identifier}, configuration),
					editingMode,
					previewMode;
				delete modeConfiguration.position;
				if (modeConfiguration.isEditingMode === true) {
					delete modeConfiguration.editingMode;
					editingMode = EditingMode.create(modeConfiguration);
					editingModes.push(editingMode);
				}
				if (modeConfiguration.isPreviewMode === true) {
					delete modeConfiguration.previewMode;
					previewMode = PreviewMode.create(modeConfiguration);
					previewModes.push(previewMode);
				}
				if (identifier === storedActiveViewModeIdentifier) {
					currentlyActiveMode = editingMode || previewMode;
				}
			});
			this.set('editingModes', editingModes);
			this.set('previewModes', previewModes);

			currentlyActiveMode = currentlyActiveMode ? currentlyActiveMode : editingModes[0];
			if (!currentlyActiveMode) {
				return;
			}
			this.set('currentlyActiveMode', currentlyActiveMode);
			Ember.sendEvent(currentlyActiveMode, 'activateOnPageLoad');
		}.observes('configuration').on('init'),

		_editPreviewPanelModeChanged: function() {
			LocalStorage.setItem('editPreviewPanelMode', this.get('editPreviewPanelMode'));
		}.observes('editPreviewPanelMode'),

		actions: {
			activateMode: function(mode) {
				this.set('currentlyActiveMode', mode);
			}
		}
	}).create();
});