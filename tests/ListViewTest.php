<?php

declare(strict_types=1);

namespace Yii\Extension\GridView\Tests\Column;

use Yii\Extension\GridView\DataProvider\ArrayDataProvider;
use Yii\Extension\GridView\Exception\InvalidConfigException;
use Yii\Extension\GridView\ListView;
use Yii\Extension\GridView\Tests\TestCase;

final class ListViewTest extends TestCase
{
    public function testAfterItemBeforeItem(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->beforeItem(static fn () =>  '<div class="testMe">')
            ->afterItem(static fn () => '</div>')
            ->dataProvider($dataProvider);

        $html = <<<'HTML'
        <div class="list-view"><div class="testMe">
        <div data-key="0">0</div>
        </div>
        <div class="testMe">
        <div data-key="1">1</div>
        </div>
        <div class="testMe">
        <div data-key="2">2</div>
        </div>
        <div class="testMe">
        <div data-key="3">3</div>
        </div>
        <div class="testMe">
        <div data-key="4">4</div>
        </div>
        <div class="testMe">
        <div data-key="5">5</div>
        </div>
        <div class="testMe">
        <div data-key="6">6</div>
        </div>
        <div class="testMe">
        <div data-key="7">7</div>
        </div>
        <div class="testMe">
        <div data-key="8">8</div>
        </div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testDataProviderEmpty(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "dataProvider" property must be set.');
        $listView = ListView::widget()->render();
    }

    public function testItemViewAsString(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->itemView('//_listview')
            ->dataProvider($dataProvider);

        $html = <<<'HTML'
        <div class="list-view"><div data-key="0"><div>1</div><div>tests 1</div><div>10</div>
        </div>
        <div data-key="1"><div>2</div><div>tests 2</div><div>20</div>
        </div>
        <div data-key="2"><div>3</div><div>tests 3</div><div>30</div>
        </div>
        <div data-key="3"><div>4</div><div>tests 4</div><div>40</div>
        </div>
        <div data-key="4"><div>5</div><div>tests 5</div><div>50</div>
        </div>
        <div data-key="5"><div>6</div><div>tests 6</div><div>60</div>
        </div>
        <div data-key="6"><div>7</div><div>tests 7</div><div>70</div>
        </div>
        <div data-key="7"><div>8</div><div>tests 8</div><div>80</div>
        </div>
        <div data-key="8"><div>9</div><div>tests 9</div><div>90</div>
        </div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testItemViewAsCallable(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->itemView(
                static fn ($model) =>
                    '<div>' . $model['id'] . '</div>' .
                    '<div>' . $model['username'] . '</div>' .
                    '<div>' . $model['total'] . '</div>'
            )
            ->dataProvider($dataProvider);

        $html = <<<'HTML'
        <div class="list-view"><div data-key="0"><div>1</div><div>tests 1</div><div>10</div></div>
        <div data-key="1"><div>2</div><div>tests 2</div><div>20</div></div>
        <div data-key="2"><div>3</div><div>tests 3</div><div>30</div></div>
        <div data-key="3"><div>4</div><div>tests 4</div><div>40</div></div>
        <div data-key="4"><div>5</div><div>tests 5</div><div>50</div></div>
        <div data-key="5"><div>6</div><div>tests 6</div><div>60</div></div>
        <div data-key="6"><div>7</div><div>tests 7</div><div>70</div></div>
        <div data-key="7"><div>8</div><div>tests 8</div><div>80</div></div>
        <div data-key="8"><div>9</div><div>tests 9</div><div>90</div></div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testItemViewOptions(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->itemView(
                static fn ($model) =>
                    '<div>' . $model['id'] . '</div>' .
                    '<div>' . $model['username'] . '</div>' .
                    '<div>' . $model['total'] . '</div>'
            )
            ->itemViewOptions(['class' => 'text-danger'])
            ->dataProvider($dataProvider);

        $html = <<<'HTML'
        <div class="list-view"><div class="text-danger" data-key="0"><div>1</div><div>tests 1</div><div>10</div></div>
        <div class="text-danger" data-key="1"><div>2</div><div>tests 2</div><div>20</div></div>
        <div class="text-danger" data-key="2"><div>3</div><div>tests 3</div><div>30</div></div>
        <div class="text-danger" data-key="3"><div>4</div><div>tests 4</div><div>40</div></div>
        <div class="text-danger" data-key="4"><div>5</div><div>tests 5</div><div>50</div></div>
        <div class="text-danger" data-key="5"><div>6</div><div>tests 6</div><div>60</div></div>
        <div class="text-danger" data-key="6"><div>7</div><div>tests 7</div><div>70</div></div>
        <div class="text-danger" data-key="7"><div>8</div><div>tests 8</div><div>80</div></div>
        <div class="text-danger" data-key="8"><div>9</div><div>tests 9</div><div>90</div></div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testOptions(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->itemView(
                static fn ($model) =>
                    '<div>' . $model['id'] . '</div>' .
                    '<div>' . $model['username'] . '</div>' .
                    '<div>' . $model['total'] . '</div>'
            )
            ->Options(['class' => 'list-view', 'tag' => 'article'])
            ->dataProvider($dataProvider);

        $html = <<<'HTML'
        <article class="list-view"><div data-key="0"><div>1</div><div>tests 1</div><div>10</div></div>
        <div data-key="1"><div>2</div><div>tests 2</div><div>20</div></div>
        <div data-key="2"><div>3</div><div>tests 3</div><div>30</div></div>
        <div data-key="3"><div>4</div><div>tests 4</div><div>40</div></div>
        <div data-key="4"><div>5</div><div>tests 5</div><div>50</div></div>
        <div data-key="5"><div>6</div><div>tests 6</div><div>60</div></div>
        <div data-key="6"><div>7</div><div>tests 7</div><div>70</div></div>
        <div data-key="7"><div>8</div><div>tests 8</div><div>80</div></div>
        <div data-key="8"><div>9</div><div>tests 9</div><div>90</div></div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </article>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testRender(): void
    {
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()->dataProvider($dataProvider);

        $html = <<<'HTML'
        <div class="list-view"><div data-key="0">0</div>
        <div data-key="1">1</div>
        <div data-key="2">2</div>
        <div data-key="3">3</div>
        <div data-key="4">4</div>
        <div data-key="5">5</div>
        <div data-key="6">6</div>
        <div data-key="7">7</div>
        <div data-key="8">8</div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testSeparator(): void
    {
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()->dataProvider($dataProvider)->separator("\n");

        $html = <<<'HTML'
        <div class="list-view"><div data-key="0">0</div>
        <div data-key="1">1</div>
        <div data-key="2">2</div>
        <div data-key="3">3</div>
        <div data-key="4">4</div>
        <div data-key="5">5</div>
        <div data-key="6">6</div>
        <div data-key="7">7</div>
        <div data-key="8">8</div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }

    public function testViewParams(): void
    {
        ListView::counter(0);

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allData($this->getArrayData());

        $listView = ListView::widget()
            ->dataProvider($dataProvider)
            ->itemView('//_listview_params')
            ->viewParams(['itemClass' => 'text-success']);

        $html = <<<'HTML'
        <div class="list-view"><div data-key="0"><div class=text-success>1</div>
        </div>
        <div data-key="1"><div class=text-success>2</div>
        </div>
        <div data-key="2"><div class=text-success>3</div>
        </div>
        <div data-key="3"><div class=text-success>4</div>
        </div>
        <div data-key="4"><div class=text-success>5</div>
        </div>
        <div data-key="5"><div class=text-success>6</div>
        </div>
        <div data-key="6"><div class=text-success>7</div>
        </div>
        <div data-key="7"><div class=text-success>8</div>
        </div>
        <div data-key="8"><div class=text-success>9</div>
        </div>
        <div class="summary">Showing <b>1-9</b> of <b>9</b> items</div>
        </div>
        HTML;

        $this->assertEqualsWithoutLE($html, $listView->render());
    }
}
