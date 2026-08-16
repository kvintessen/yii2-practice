<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;

final class CategoryTest extends \Codeception\Test\Unit
{
    public function testValidationRequiresNameAndSlug()
    {
        $category = new Category();

        verify($category->validate())->false();
        verify($category->errors)->arrayHasKey('name');
        verify($category->errors)->arrayHasKey('slug');
    }

    public function testSlugMustBeUnique()
    {
        $original = new Category(['name' => 'Electronics', 'slug' => 'electronics']);
        verify($original->save())->true();

        $duplicate = new Category(['name' => 'Electronics again', 'slug' => 'electronics']);

        verify($duplicate->validate())->false();
        verify($duplicate->errors)->arrayHasKey('slug');
    }

    public function testParentChildRelation()
    {
        $parent = new Category(['name' => 'Electronics', 'slug' => 'electronics']);
        verify($parent->save())->true();

        $child = new Category(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => $parent->id]);
        verify($child->save())->true();

        verify($child->parent->id)->equals($parent->id);
        verify(count($parent->children))->equals(1);
        verify($parent->children[0]->id)->equals($child->id);
    }

    public function testParentMustExist()
    {
        $category = new Category(['name' => 'Phones', 'slug' => 'phones', 'parent_id' => 999999]);

        verify($category->validate())->false();
        verify($category->errors)->arrayHasKey('parent_id');
    }
}
