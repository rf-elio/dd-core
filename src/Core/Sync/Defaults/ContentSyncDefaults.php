<?php
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\ElioDataDiscovery\Core\Sync\Defaults;


/**
 * Class ContentSyncDefaults
 * @package Elio\ElioDataDiscovery\Core\Sync\Defaults
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
abstract class ContentSyncDefaults
{
    public const TYPE = 'content';
    public const FIELD_ID = 'Id';
    public const FIELD_TYPE = 'Type';
    public const FIELD_NAME = 'Name';
    public const FIELD_TITLE = 'Title';
    public const FIELD_SEO_TEXT = 'SeoText';
    public const FIELD_URL = 'Url';
    public const FIELD_KEYWORDS = 'Keywords';
    public const FIELD_DESCRIPTION = 'Description';
    public const FIELD_IMAGE_URL = 'ImageUrl';
    public const FIELD_PUBLICATION_DATE = 'PublicationDate';
    public const FIELD_PRIORITY = 'Priority';
    public const FIELD_CONTENT_STRUCTURE = 'ContentStructure';
    public const FIELD_TYPE_DEFAULT = 'category';
    public const FIELD_TAGS = 'Tags';

    public const DEFAULT_PRIORITY = 50;
}