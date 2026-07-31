START TRANSACTION;

UPDATE config
SET data = REPLACE(data, 's:17:"gsoc_ai_moderator"', 's:19:"toxic_spam_detector"')
WHERE name = 'core.extension';

UPDATE key_value
SET value = REPLACE(value, 's:38:"gsoc_ai_moderator_form_node_form_alter"', 's:42:"toxic_spam_detector_form_node_form_alter"')
WHERE collection = 'hook_data' AND name = 'hook_list';

UPDATE key_value
SET value = REPLACE(value, 's:36:"gsoc_ai_moderator_node_form_validate"', 's:40:"toxic_spam_detector_node_form_validate"')
WHERE collection = 'hook_data' AND name = 'hook_list';

UPDATE key_value
SET value = REPLACE(value, 's:32:"gsoc_ai_moderator_entity_presave"', 's:36:"toxic_spam_detector_entity_presave"')
WHERE collection = 'hook_data' AND name = 'hook_list';

UPDATE key_value
SET value = REPLACE(value, 's:17:"gsoc_ai_moderator"', 's:19:"toxic_spam_detector"')
WHERE collection = 'hook_data' AND name = 'hook_list';

UPDATE key_value
SET name = 'toxic_spam_detector'
WHERE collection = 'system.schema' AND name = 'gsoc_ai_moderator';

INSERT INTO config (collection, name, data)
SELECT collection, 'toxic_spam_detector.settings', data
FROM config
WHERE name = 'gsoc_ai_moderator.settings'
AND NOT EXISTS (SELECT 1 FROM config c2 WHERE c2.name = 'toxic_spam_detector.settings');

DELETE FROM config WHERE name = 'gsoc_ai_moderator.settings';

COMMIT;

TRUNCATE cache_bootstrap;
TRUNCATE cache_config;
TRUNCATE cache_container;
TRUNCATE cache_discovery;
TRUNCATE cache_default;
TRUNCATE cache_data;
TRUNCATE cache_entity;
TRUNCATE cache_menu;
TRUNCATE cache_render;
TRUNCATE cache_page;
TRUNCATE cache_dynamic_page_cache;
TRUNCATE cache_toolbar;
TRUNCATE cache_access_policy;
