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

namespace Elio\FactFinder\Core\FilterRestrictions;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Elio\FactFinder\Core\FilterRestrictions\FilterRestrictionsEntity;
use Elio\FactFinder\Core\FilterRestrictions\FilterRestrictionsCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

/**
 * Class FilterRestrictionsDefinition
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterRestrictionsDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'elio_ff_filter_restrictions';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FilterRestrictionsEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FilterRestrictionsCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
           (new IdField('id', 'id'))->addFlags(
               new ApiAware(),
               new Required(),
               new PrimaryKey()
           ),
           /**
            * the restriction can be applied to category or 'enum' layer - isCategory showing which one should be used
            * and layer or categoryId for the reference
            */
           (new BoolField('is_category', 'isCategory'))->addFlags(
               new ApiAware(),
               new Required()
           ),
           (new StringField('layer', 'layer'))->addFlags(new ApiAware()),
           (new FkField('category_id', 'categoryId', CategoryDefinition::class))->addFlags(
               new ApiAware()
           ),
           (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(
               new ApiAware()
           ),
           /**
            * is it collection of filters for allowed or blocked column
            */
           (new BoolField('is_allowed', 'isAllowed'))->addFlags(
               new ApiAware(),
               new Required()
           ),
           /**
            * is it inherited from all-saleschannel restriction
            */
           (new BoolField('is_inherited', 'isInherited'))->addFlags(
               new ApiAware(),
               new Required()
           ),
           (new BoolField('is_all_checked', 'isAllChecked'))->addFlags(
               new ApiAware(),
               new Required()
           ),
           new OneToOneAssociationField(
               'salesChannel',
               'sales_channel_id',
               'id',
               SalesChannelDefinition::class,
               false
           ),
           new OneToOneAssociationField(
               'category',
               'category_id',
               'id',
               CategoryDefinition::class,
               false
           ),
           new ManyToManyAssociationField(
               'filters',
               FilterDefinition::class,
               FilterRestrictionsFilterMapping::class,
               'filter_restriction_id',
               'filter_id'
           )
       ]);
    }
}