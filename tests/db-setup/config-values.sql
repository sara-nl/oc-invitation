-- The provider domain of this instance, equal to its trusted server domain value
-- Eg. trusted server value is'https://rd-1.nl', then provider domain is 'rd-1.nl'
INSERT INTO `oc_appconfig` (`appid`, `configkey`, `configvalue`) VALUES ('rd-mesh', 'domain', 'rd-1.nl') 
    ON DUPLICATE KEY UPDATE `configvalue`='rd-1.nl';
