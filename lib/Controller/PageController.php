<?php

/**
 * 
 */

namespace OCA\RDMesh\Controller;

use OCA\Federation\TrustedServers;
use OCA\RDMesh\Service\MeshService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class PageController extends Controller
{

   private TrustedServers $trustedServers;

   public function __construct($appName, IRequest $request, TrustedServers $trustedServers)
   {
      parent::__construct($appName, $request);
      $this->trustedServers = $trustedServers;
   }

   /**
    * Displays the WAYF page.
    * 
    * @NoCSRFRequired
    * @PublicPage
     * @param string $token the token
     * @param string $senderDomain the domain of the sender
     * @param string $senderEmail the email of the sender
     * @return void
    */
   public function wayf(string $token, string $senderDomain, string $senderEmail): void
   {

      echo '<html title="WAYF"><head></head><h4>Where Are You From</h4>';
      // TODO: retrieve the mesh servers info from the db
      foreach ($this->getWAYFURLs($token, $senderDomain, $senderEmail) as $i => $url) {
         echo print_r("<a href=\"$url\">$url</a>", true) . '</html>';
      }
      echo '</html>';

      exit(0);
   }

   /**
    * Returns the WAYF URLs.
    */
   private function getWAYFURLs(string $token, string $senderDomain, string $senderEmail): array
   {
      // get the mesh
      $trustedServers = $this->trustedServers->getServers();
      $wayfList = [];
      foreach ($trustedServers as $i => $server) {

         // FIXME: request the full owncloud root url, ie. the part before /apps/
         //                     |
         //                     v
         //        +----------------------------+
         //        |                            |
         //        https://owncloud.mydomain.org/apps/rd-mesh/...
         //
         // TODO: We must fully figure out how to deal with domains, hosts, and designing the request functions for these.
         //       plus if required the configuration of these.

         $host = parse_url($server['url'], PHP_URL_HOST);

         // TODO: check if the server supports the invitation workflow

         $appName = $this->appName;
         $handleInviteEndpoint = trim(MeshService::ENDPOINT_HANDLE_INVITE, '/');
         $tokenParam = MeshService::PARAM_NAME_TOKEN;
         $senderDomainParam = MeshService::PARAM_NAME_SENDER_DOMAIN;
         $senderEmailParam = MeshService::PARAM_NAME_SENDER_EMAIL;
         $link = "https://$host/apps/$appName/$handleInviteEndpoint?$tokenParam=$token&$senderDomainParam=$senderDomain&$senderEmailParam=$senderEmail";
         $wayfList[$i] = $link;
      }

      return $wayfList;
   }
}
