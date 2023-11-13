SET NAMES utf8mb4;

-- The domain providers, ie. 'the mesh'
INSERT INTO `oc_mesh_domain_providers` 
    (`domain`)
VALUES
    ('rd-1.nl'),
    ('rd-2.nl'),
    ('rd-3.nl'),
    ('rd-4.nl');

-- App configuration values
INSERT INTO `oc_appconfig` 
    (`appid`, `configkey`, `configvalue`)
VALUES
    ('invitation', 'domain', 'rd-1.nl')
ON DUPLICATE KEY UPDATE
    configkey= 'domain',
    configvalue = 'rd-1.nl';

INSERT INTO `oc_appconfig` 
    (`appid`, `configkey`, `configvalue`)
VALUES
    ('invitation', 'allow_sharing_with_non_invited_users', 'yes')
ON DUPLICATE KEY UPDATE
    configkey= 'allow_sharing_with_non_invited_users',
    configvalue = 'yes';
