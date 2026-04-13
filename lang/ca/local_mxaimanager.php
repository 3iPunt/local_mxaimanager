<?php

$string['pluginname'] = 'Moxis AI Manager';

// Manage Providers
$string['manage:info'] = "<p>Aquesta configuració et permetrà definir quins proveïdors d'IA (OpenAI, Mistral, etc.) estan disponibles al teu lloc.</p><p>També podràs configurar:</p><ul><li>Quina instància de proveïdor s'ha d'utilitzar per defecte.</li><li>Quin model ha d'utilitzar una instància de proveïdor per defecte.</li><li>Quin model i/o quina instància de proveïdor s'ha d'utilitzar per a una funció d'IA específica al teu connector d'IA.</li></ul>";
$string['manage_providers:title'] = 'Instàncies de proveïdors d\'IA';
$string['manage_providers:table:name'] = "Nom de la instància";
$string['manage_providers:table:classname'] = "Tipus de proveïdor";
$string['manage_providers:table:supported_actions'] = "Accions suportades";
$string['manage_providers:table:actions'] = "Accions";
$string['manage_providers:form:name'] = 'Nom';
$string['manage_providers:form:type'] = 'Tipus';
$string['manage_providers:add_provider'] = 'Afegeix una instància de proveïdor d\'IA';
$string['manage_providers:edit_provider'] = 'Edita la instància del proveïdor d\'IA';
$string['manage_providers:delete_provider'] = 'Elimina la instància del proveïdor d\'IA: "{$a}"';
$string['manage_providers:delete_confirm'] = 'Estàs segur que vols eliminar aquesta instància de proveïdor? Aquesta acció no es pot desfer.';
$string['here_you_define_providers'] = 'Aquí defineixes les instàncies de proveïdors d\'IA que estaran disponibles al teu lloc.';
$string['set_as_default'] = 'Estableix com a predeterminat?';
$string['in_use'] = 'Ja en ús';

// Provider options help texts
$string['openai_chat_model'] = 'Model de xat d\'OpenAI';
$string['openai_chat_model_help'] = 'Aquí pots especificar el model de xat que s\'ha d\'utilitzar. Per exemple: <strong>gpt-4</strong>, <strong>gpt-3.5-turbo</strong>, etc. Consulta la documentació d\'OpenAI per als models disponibles.';
$string['mistral_chat_model'] = 'Model de xat de Mistral';
$string['mistral_chat_model_help'] = 'Aquí pots especificar el model de xat que s\'ha d\'utilitzar. Per exemple: <strong>mistral-large</strong>, <strong>mistral-small</strong>, etc. Consulta la documentació de Mistral per als models disponibles.';
$string['ollama_chat_model'] = 'Model de xat d\'Ollama';
$string['ollama_chat_model_help'] = 'Aquí pots especificar el model de xat que s\'ha d\'utilitzar. Per exemple: <strong>llama2</strong>, <strong>vicuna</strong>, etc. Consulta el teu proveïdor d\'Ollama per als models disponibles.';
$string['nebius_chat_model'] = 'Model de xat de Nebius';
$string['nebius_chat_model_help'] = 'Aquí pots especificar el model de xat que s\'ha d\'utilitzar. Per exemple: <strong>Qwen/Qwen3-32B-fast</strong>, <strong>Qwen/Qwen3-30B-A3B-Instruct-2507</strong>, etc. Consulta la documentació de Nebius per als models disponibles.';
$string['openai_embedding_model'] = 'Model d\'embedding d\'OpenAI';
$string['openai_embedding_model_help'] = 'Aquí pots especificar el model d\'embedding que s\'ha d\'utilitzar. Per exemple: <strong>text-embedding-3-small</strong>, <strong>text-embedding-3-large</strong>, etc. Consulta la documentació d\'OpenAI per als models d\'embedding disponibles.';
$string['mistral_embedding_model'] = 'Model d\'embedding de Mistral';
$string['mistral_embedding_model_help'] = 'Aquí pots especificar el model d\'embedding que s\'ha d\'utilitzar. Per exemple: <strong>mistral-embed</strong>, etc. Consulta la documentació de Mistral per als models d\'embedding disponibles.';
$string['ollama_embedding_model'] = 'Model d\'embedding d\'Ollama';
$string['ollama_embedding_model_help'] = 'Aquí pots especificar el model d\'embedding que s\'ha d\'utilitzar. Per exemple: <strong>nomic-embed-text</strong>, etc. Consulta el teu proveïdor d\'Ollama per als models d\'embedding disponibles.';
$string['nebius_embedding_model'] = 'Model d\'embedding de Nebius';
$string['nebius_embedding_model_help'] = 'Aquí pots especificar el model d\'embedding que s\'ha d\'utilitzar. Per exemple: <strong>Qwen/Qwen3-Embedding-8B</strong>, etc. Consulta la documentació de Nebius per als models d\'embedding disponibles.';
$string['openai_image_model'] = 'Model d\'imatge d\'OpenAI';
$string['openai_image_model_help'] = 'Aquí pots especificar el model de generació d\'imatges que s\'ha d\'utilitzar. Per exemple: <strong>dall-e-3</strong>, <strong>dall-e-2</strong>, etc. Consulta la documentació d\'OpenAI per als models de generació d\'imatges disponibles.';
$string['nebius_image_model'] = 'Model d\'imatge de Nebius';
$string['nebius_image_model_help'] = 'Aquí pots especificar el model de generació d\'imatges que s\'ha d\'utilitzar. Per exemple: <strong>black-forest-labs/flux-dev</strong>. Consulta la documentació de Nebius per als models de generació d\'imatges disponibles.';
$string['openai_transcription_model'] = 'Model de transcripció d\'OpenAI';
$string['openai_transcription_model_help'] = 'Aquí pots especificar el model de transcripció que s\'ha d\'utilitzar. Per exemple: <strong>whisper-1</strong>. Consulta la documentació d\'OpenAI per als models de transcripció disponibles.';
$string['mistral_transcription_model'] = 'Model de transcripció de Mistral';
$string['mistral_transcription_model_help'] = 'Aquí pots especificar el model de transcripció que s\'ha d\'utilitzar. Per exemple: <strong>mistral-whisper</strong>. Consulta la documentació de Mistral per als models de transcripció disponibles.';

// Manage Features
$string['manage_features:title'] = 'Funcions d\'IA';
$string['manage_features:table:component'] = 'Component';
$string['manage_features:table:name'] = 'Nom';
$string['manage_features:table:description'] = 'Descripció';
$string['manage_features:table:ai_actions'] = 'Accions d\'IA requerides';
$string['manage_features:table:actions'] = 'Accions';
$string['manage_features:edit_feature_settings'] = 'Edita la configuració de la funció d\'IA: "{$a}"';
$string['manage_features:form:provider_id'] = 'Instància de proveïdor';
$string['here_you_can_see_all_components_ai_features'] = 'Aquí pots veure totes les funcions d\'IA dels components que estan disponibles al teu lloc. Pots sobreescriure la instància de proveïdor per defecte i/o la configuració per a cada funció.';
$string['uses_chat'] = 'Xat';
$string['uses_embedding'] = 'Embeddings';
$string['uses_image'] = 'Imatge';
$string['uses_audio_transcriptions'] = 'Transcripcions d\'àudio';

$string['base_url'] = 'URL base';
$string['api_key'] = 'Clau d\'API';
$string['api_version'] = 'Versió de l\'API';
$string['default_chat_model'] = 'Model de xat per defecte';
$string['default_embedding_model'] = 'Model d\'embedding per defecte';
$string['default_image_model'] = 'Model d\'imatge per defecte';
$string['default_transcription_model'] = 'Model de transcripció per defecte';
$string['provider_settings'] = 'Configuració del proveïdor';
$string['supports_chat'] = 'Suporta xat';
$string['supports_embedding'] = 'Suporta embedding';
$string['supports_image'] = 'Suporta imatge';
$string['supports_audio_transcriptions'] = 'Suporta transcripcions d\'àudio';
$string['provider_supports'] = 'Capacitats del proveïdor';
$string['default_action_providers'] = 'Instàncies de proveïdor d\'acció per defecte';
$string['here_you_define_default_action_providers'] = 'Aquí defineixes quines instàncies de proveïdor s\'han d\'utilitzar per defecte per a cada acció.';
$string['you_have_configured_a_provider_and_set_the_default'] = 'Has configurat una instància de proveïdor i has establert la instància de proveïdor per defecte per a totes les accions. Ja estàs a punt per utilitzar les funcions d\'IA als teus connectors d\'IA! :)';
$string['you_have_not_yet_configured_any_providers'] = 'Encara no has configurat cap instància de proveïdor d\'IA. Si us plau, afegeix almenys un proveïdor <a href="/local/mxaimanager/view.php?view=manage_providers&action=browse">aquí</a>.';
$string['you_have_not_yet_configured_default_providers'] = 'Encara no has configurat les instàncies de proveïdor per defecte per a totes les accions. Si us plau, configura les instàncies de proveïdor per defecte <a href="/local/mxaimanager/view.php?view=manage_providers&action=browse">aquí</a>.';
$string['no_available_providers'] = 'No hi ha instàncies de proveïdor disponibles';
$string['this_provider_is_preconfigured_no_modify'] = 'Aquesta instància de proveïdor està preconfigurada i no es pot modificar.';

// Gemini Error Messages
$string['gemini_missing_api_key'] = 'A Gemini li falta la clau de l\'API';
$string['gemini_chat_model_not_configured'] = 'El model de xat no està configurat';
$string['gemini_missing_chat_content'] = 'Falta contingut a la resposta de Gemini. Resposta de Gemini: {$a}';
$string['gemini_invalid_response'] = 'Resposta no vàlida de Gemini: {$a}';
$string['gemini_embedding_model_not_configured'] = 'El model d\'embedding no està configurat';
$string['gemini_missing_embedding_data'] = 'Falten dades d\'embedding a la resposta de Gemini. Resposta de Gemini: {$a}';
$string['gemini_image_model_not_configured'] = 'El model d\'imatge no està configurat';
$string['gemini_image_b64_only'] = 'L\'API de Gemini només admet la generació d\'imatges en base64.';
$string['gemini_missing_image_data'] = 'Falten dades d\'imatge a la resposta de Gemini. Resposta de Gemini: {$a}';
$string['gemini_transcription_model_not_configured'] = 'El model de transcripció no està configurat';
$string['gemini_missing_transcription_data'] = 'Falten dades de transcripció a la resposta de Gemini. Resposta de Gemini: {$a}';

// Copilot Error Messages
$string['copilot_missing_base_url_or_api_key'] = 'A Copilot (Azure OpenAI) li falta la URL base i/o la clau de l\'API';
$string['copilot_chat_model_not_configured'] = 'El model de xat no està configurat';
$string['copilot_missing_chat_content'] = 'Falta contingut a la resposta de Copilot (Azure OpenAI). Resposta: {$a}';
$string['copilot_invalid_response'] = 'Resposta no vàlida de Copilot (Azure OpenAI): {$a}';
$string['copilot_embedding_model_not_configured'] = 'El model d\'embedding no està configurat';
$string['copilot_missing_embedding_data'] = 'Falten dades d\'embedding a la resposta de Copilot (Azure OpenAI). Resposta: {$a}';
$string['copilot_image_model_not_configured'] = 'El model d\'imatge no està configurat';
$string['copilot_missing_image_data'] = 'Falten dades d\'imatge a la resposta de Copilot (Azure OpenAI). Resposta: {$a}';
$string['copilot_invalid_response_image_generation'] = 'Resposta no vàlida de la generació d\'imatges de Copilot (Azure OpenAI): {$a}';
$string['copilot_transcription_model_not_configured'] = 'El model de transcripció no està configurat';
$string['copilot_missing_transcription_data'] = 'Falta text a la resposta de transcripció de Copilot (Azure OpenAI). Resposta: {$a}';
$string['copilot_invalid_response_transcription'] = 'Resposta no vàlida de la transcripció de Copilot (Azure OpenAI): {$a}';

// Settings
$string['settings:manage_page'] = 'Gestiona la configuració d\'IA';

// Capabilities
$string['mxaimanager:manage_configuration'] = 'Gestiona la configuració de Moxis AI Manager';

// Privacy
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs'] = 'Aquesta taula emmagatzema els registres d\'ús de les accions de les funcions per al connector Moxis AI Manager.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:id'] = 'ID';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:feature_id'] = 'L\'ID de la funció d\'IA que s\'ha utilitzat.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:request_json'] = 'La sol·licitud JSON enviada al proveïdor d\'IA.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:response_json'] = 'La resposta JSON rebuda del proveïdor d\'IA.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:input_tokens'] = 'El nombre de tokens d\'entrada utilitzats a la sol·licitud.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:output_tokens'] = 'El nombre de tokens de sortida rebuts a la resposta.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:session_id'] = 'L\'ID de la sessió associada a la sol·licitud.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:user_id'] = 'L\'ID de l\'usuari que ha realitzat la sol·licitud.';
$string['privacy:metadata:local_mxaimanager_feature_action_usage_logs:timecreated'] = 'La marca de temps de quan es va crear l\'entrada de registre.';
