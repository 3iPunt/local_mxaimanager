<?php

$string['pluginname'] = 'Moxis AI Manager';

// Manage Providers
$string['manage:info'] = "<p>These settings will make it possible for you to define which AI providers (OpenAI, Mistral, eg.) are available on your site.</p><p>You'll also be able to configure:</p><ul><li>Which provider instance should be used by default.</li><li>Which model a provider instance should use by default.</li><li>Which model and/or just which provider instance should be used for a specific AI feature in your AI plugin.</li></ul>";
$string['manage_providers:title'] = 'AI Provider instances';
$string['manage_providers:table:name'] = "Instance Name";
$string['manage_providers:table:type'] = "Provider Type";
$string['manage_providers:table:supported_actions'] = "Supported Actions";
$string['manage_providers:table:actions'] = "Actions";
$string['manage_providers:form:name'] = 'Name';
$string['manage_providers:form:type'] = 'Type';
$string['manage_providers:add_provider'] = 'Add AI Provider instance';
$string['manage_providers:edit_provider'] = 'Edit AI Provider instance';
$string['manage_providers:delete_provider'] = 'Delete AI Provider instance: "{$a}"';
$string['manage_providers:delete_confirm'] = 'Are you sure you want to delete this provider instance? This action cannot be undone.';
$string['here_you_define_providers'] = 'Here you define the AI provider instances that will be available on your site.';
$string['set_as_default'] = 'Set as default?';
$string['in_use'] = 'Already in use';

// Manage Features
$string['manage_features:title'] = 'AI Features';
$string['manage_features:table:component'] = 'Component';
$string['manage_features:table:name'] = 'Name';
$string['manage_features:table:description'] = 'Description';
$string['manage_features:table:ai_actions'] = 'Required AI Actions';
$string['manage_features:table:actions'] = 'Actions';
$string['here_you_can_see_all_components_ai_features'] = 'Here you can see all components\' AI features that are available on your site. You can override the default provider instance and/or settings for each feature.';
$string['uses_chat'] = 'Chat';
$string['uses_embedding'] = 'Embeddings';

$string['base_url'] = 'Base URL';
$string['api_key'] = 'API Key';
$string['default_chat_model'] = 'Default Chat Model';
$string['default_embedding_model'] = 'Default Embedding Model';
$string['provider_settings'] = 'Provider Settings';
$string['supports_chat'] = 'Supports Chat';
$string['supports_embedding'] = 'Supports Embedding';
$string['provider_supports'] = 'Provider Capabilities';
$string['default_action_providers'] = 'Default Action Provider instances';
$string['here_you_define_default_action_providers'] = 'Here you define which provider instances should be used by default for each action.';
$string['you_have_configured_a_provider_and_set_the_default'] = 'You have configured a provider instance and set the default provider instance for all actions. You are now ready to use AI features in your AI plugins! :)';
$string['you_have_not_yet_configured_any_providers'] = 'You\'ve not yet configured any AI provider instances. Please add at least one provider <a href="/local/mxaimanager/view.php?view=manage_providers&action=browse">here</a>.';
$string['you_have_not_yet_configured_default_providers'] = 'You\'ve not yet configured default provider instances for all actions. Please configure default provider instances <a href="/local/mxaimanager/view.php?view=manage_providers&action=browse">here</a>.';
$string['no_available_providers'] = 'No available provider instances';

// Settings
$string['settings:manage_page'] = 'Manage AI Settings';

// Privacy
$string['privacy:metadata'] = 'local_mxaimanager has no userdata';
