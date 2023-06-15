<?php

/**
 * @file
 * Hooks specific to the DIPAS module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide routes that should be accessible for anonymous users.
 *
 * @returns string[]
 *   Routes or pcre patterns describing routes
 */
function hook_open_dipas_routes() {
  return [
    'static.open.route',
    '~route\.pattern\..*~',
  ];
}

/**
 * Provide slugs that should get ignored by the DIPAS Rest API
 *
 * @returns string[]
 *   Collection of URL slugs that should not get handled by the DIPAS Rest API
 */
function hook_dipas_api_links() {
  return [
    'customslug' // DIPAS module ignores requests to /dipas/customslug(/*)
  ];
}

/**
 * Provide Masterportal instance configurations that should NOT get cloned for new domains.
 *
 * @return string[]
 */
function hook_dipas_noclone_masterportal_instances() {
  return [
    'instance_name'
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
