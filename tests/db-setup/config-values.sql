INSERT INTO `oc_appconfig` (`appid`, `configkey`, `configvalue`) VALUES ('rd-mesh', 'domain', 'rd-1.nl') 
    ON DUPLICATE KEY UPDATE `configvalue`='rd-1.nl';
