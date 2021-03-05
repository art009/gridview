<?php

declare(strict_types=1);

namespace Yii\Extension\GridView\Widget;

use JsonException;
use Yii\Extension\GridView\DataProvider\DataProviderInterface;
use Yii\Extension\GridView\Exception\InvalidConfigException;
use Yii\Extension\GridView\Factory\GridViewFactory;
use Yii\Extension\GridView\Helper\Html;
use Yii\Extension\GridView\Helper\Pagination;
use Yii\Extension\GridView\Helper\Sort;
use Yii\Extension\GridView\Widget;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * BaseListView is a base class for widgets displaying data from data provider such as ListView and GridView.
 *
 * It provides features like sorting, paging and also filtering the data.
 *
 * For more details and usage information on BaseListView, see the:
 *
 * [guide article on data widgets](guide:output-data-widgets).
 */
abstract class BaseListView extends Widget
{
    public const BOOTSTRAP = 'bootstrap';
    public const BULMA = 'bulma';
    protected DataProviderInterface $dataProvider;
    protected string $emptyText = 'No results found.';
    protected string $frameworkCss = self::BOOTSTRAP;
    protected GridViewFactory $gridViewFactory;
    protected Html $html;
    protected string $layout = "{items}\n{summary}\n{pager}";
    protected array $options = [];
    protected Pagination $pagination;
    protected TranslatorInterface $translator;
    protected WebView $webView;
    private const FRAMEWORKCSS = [
        self::BOOTSTRAP,
        self::BULMA,
    ];
    private int $currentPage = 1;
    private bool $encloseByContainer = false;
    private array $encloseByContainerOptions = [];
    private array $emptyTextOptions = ['class' => 'empty'];
    private int $pageSize = Pagination::DEFAULT_PAGE_SIZE;
    private array $requestAttributes = [];
    private array $requestQueryParams = [];
    private bool $showOnEmpty = false;
    private string $summary = 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> ' .
        '{totalCount, plural, one{item} other{items}}';
    private array $summaryOptions = ['class' => 'summary'];

    public function __construct(
        Html $html,
        GridViewFactory $gridViewFactory,
        TranslatorInterface $translator,
        WebView $webView
    ) {
        $this->html = $html;
        $this->gridViewFactory = $gridViewFactory;
        $this->translator = $translator;
        $this->webView = $webView;
    }

    /**
     * Renders the data active record classes.
     *
     * @return string the rendering result.
     */
    abstract protected function renderItems(): string;

    protected function run(): string
    {
        if (!isset($this->dataProvider)) {
            throw new InvalidConfigException('The "dataProvider" property must be set.');
        }

        $this->pagination = $this->getPagination();

        $this->pagination->currentPage($this->currentPage);

        if ($this->pageSize > 0) {
            $this->pagination->pageSize($this->pageSize);
        }

        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback('/{\\w+}/', function (array $matches): string {
                return $this->renderSection((string) $matches[0]);
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $options = $this->options;

        /** @var string */
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        $html = $this->html->tag($tag, $content, $options);

        if ($this->encloseByContainer) {
            $html =
                $this->html->beginTag('div', $this->encloseByContainerOptions) . "\n" .
                    $this->html->tag($tag, $content, $options) . "\n" .
                $this->html->endTag('div') . "\n";
        }

        return $html;
    }

    public function currentPage(int $currentPage): self
    {
        $new = clone $this;
        $new->currentPage = $currentPage;

        return $new;
    }

    /**
     * @param DataProviderInterface $dataProvider the data provider for the view. This property is required.
     *
     * @return $this
     */
    public function dataProvider(DataProviderInterface $dataProvider): self
    {
        $new = clone $this;
        $new->dataProvider = $dataProvider;

        return $new;
    }

    public function encloseByContainer(): self
    {
        $new = clone $this;
        $new->encloseByContainer = true;

        return $new;
    }

    public function encloseByContainerOptions(array $encloseByContainerOptions): self
    {
        $new = clone $this;
        $new->encloseByContainerOptions = $encloseByContainerOptions;

        return $new;
    }

    /**
     * @param string $emptyText the HTML content to be displayed when {@see dataProvider} does not have any data.
     *
     * The default value is the text "No results found." which will be translated to the current application language.
     *
     * @return $this
     *
     * {@see notShowOnEmpty}
     * {@see emptyTextOptions}
     */
    public function emptyText(string $emptyText): self
    {
        $new = clone $this;
        $new->emptyText = $emptyText;

        return $new;
    }

    /**
     * @param array $emptyTextOptions the HTML attributes for the emptyText of the list view.
     *
     * The "tag" element specifies the tag name of the emptyText element and defaults to "div".
     *
     * @return $this
     *
     * {@see Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    public function emptyTextOptions(array $emptyTextOptions): self
    {
        $new = clone $this;
        $new->emptyTextOptions = $emptyTextOptions;

        return $new;
    }

    public function frameworkCss(string $frameworkCss): self
    {
        if (!in_array($frameworkCss, self::FRAMEWORKCSS)) {
            $frameworkCss = implode('", "', self::FRAMEWORKCSS);
            throw new InvalidConfigException("Invalid framework css. Valid values are: \"$frameworkCss\".");
        }

        $new = clone $this;
        $new->frameworkCss = $frameworkCss;

        return $new;
    }

    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getFrameworkCss(): string
    {
        return $this->frameworkCss;
    }

    public function getHtml(): Html
    {
        return $this->html;
    }

    public function getPagination(): Pagination
    {
        return $this->dataProvider->getPagination();
    }

    public function getRequestAttributes(): array
    {
        return $this->requestAttributes;
    }

    public function getRequestQueryParams(): array
    {
        return $this->requestQueryParams;
    }

    public function getSort(): Sort
    {
        return $this->dataProvider->getSort();
    }

    /**
     * @param string $layout the layout that determines how different sections of the list view should be organized.
     *
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `{summary}`: the summary section. {@see renderSummary()}.
     * - `{items}`: the list items. {@see renderItems()}.
     * - `{sorter}`: the sorter. {@see renderSorter()}.
     * - `{pager}`: the pager. {@see renderPager()}.
     *
     * @return $this
     */
    public function layout(string $layout): self
    {
        $new = clone $this;
        $new->layout = $layout;

        return $new;
    }

    /**
     * @param array $options the HTML attributes for the container tag of the list view. The "tag" element specifies
     * the tag name of the container element and defaults to "div".
     *
     * @return $this
     *
     * {@see Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    public function options(array $options): self
    {
        $new = clone $this;
        $new->options = $options;

        return $new;
    }

    public function pageSize(int $pageSize): self
    {
        $new = clone $this;
        $new->pageSize = $pageSize;

        return $new;
    }

    public function requestAttributes(array $requestAttributes): self
    {
        $new = clone $this;
        $new->requestAttributes = $requestAttributes;

        return $new;
    }

    public function requestQueryParams(array $requestQueryParams): self
    {
        $new = clone $this;
        $new->requestQueryParams = $requestQueryParams;

        return $new;
    }

    /**
     * Whether to show an empty list view if {@see dataProvider} returns no data.
     *
     * @return $this
     */
    public function showOnEmpty(): self
    {
        $new = clone $this;
        $new->showOnEmpty = true;

        return $new;
    }

    /**
     * @param string $summary the HTML content to be displayed as the summary of the list view.
     *
     * If you do not want to show the summary, you may set it with an empty string.
     *
     * The following tokens will be replaced with the corresponding values:
     *
     * - `{begin}`: the starting row number (1-based) currently being displayed.
     * - `{end}`: the ending row number (1-based) currently being displayed.
     * - `{count}`: the number of rows currently being displayed.
     * - `{totalCount}`: the total number of rows available.
     * - `{page}`: the page number (1-based) current being displayed.
     * - `{pageCount}`: the number of pages available.
     *
     * @return $this
     */
    public function summary(string $summary): self
    {
        $new = clone $this;
        $new->summary = $summary;

        return $new;
    }

    /**
     * @param array $summaryOptions the HTML attributes for the summary of the list view. The "tag" element specifies
     * the tag name of the summary element and defaults to "div".
     *
     * @return $this
     *
     * {@see Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    public function summaryOptions(array $summaryOptions): self
    {
        $new = clone $this;
        $new->summaryOptions = $summaryOptions;

        return $new;
    }

    /**
     * Renders the HTML content indicating that the list view has no data.
     *
     * @throws JsonException
     *
     * @return string the rendering result
     *
     * {@see emptyText}
     */
    protected function renderEmpty(): string
    {
        if ($this->emptyText === '') {
            return '';
        }

        $options = $this->emptyTextOptions;

        /** @var string */
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        return $this->html->tag($tag, $this->emptyText, $options);
    }

    /**
     * Renders the pager.
     *
     * @throws InvalidConfigException|JsonException
     *
     * @return string the rendering result
     */
    private function renderPager(): string
    {
        if ($this->dataProvider->getCount() < 0) {
            return '';
        }

        return LinkPager::widget()
            ->frameworkCss($this->frameworkCss)
            ->requestAttributes($this->requestAttributes)
            ->requestQueryParams($this->requestQueryParams)
            ->pagination($this->pagination)
            ->render();
    }

    /**
     * Renders a section of the specified name. If the named section is not supported, empty string will be returned.
     *
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     *
     * @throws InvalidConfigException|JsonException
     *
     * @return string the rendering result of the section, or false if the named section is not supported.
     */
    protected function renderSection(string $name): string
    {
        switch ($name) {
            case '{summary}':
                return $this->renderSummary();
            case '{items}':
                return $this->renderItems();
            case '{pager}':
                return $this->renderPager();
            case '{sorter}':
                return $this->renderSorter();
            default:
                return '';
        }
    }

    /**
     * Renders the sorter.
     *
     * @throws InvalidConfigException
     *
     * @return string the rendering result
     */
    private function renderSorter(): string
    {
        $sort = $this->dataProvider->getSort();

        if (empty($sort->getAttributeOrders()) || $this->dataProvider->getCount() <= 0) {
            return '';
        }

        return LinkSorter::widget()->sort($sort)->frameworkCss($this->frameworkCss)->render();
    }

    private function renderSummary(): string
    {
        $count = $this->dataProvider->getCount();

        if ($count <= 0) {
            return '';
        }

        $summaryOptions = $this->summaryOptions;
        $summaryOptions['encode'] = false;

        /** @var string */
        $tag = ArrayHelper::remove($summaryOptions, 'tag', 'div');

        $totalCount = $this->dataProvider->getTotalCount();
        $begin = ($this->pagination->getOffset() + 1);
        $end = $begin + $count - 1;

        if ($begin > $end) {
            $begin = $end;
        }

        $page = $this->pagination->getCurrentPage();
        $pageCount = $this->pagination->getTotalPages();

        return $this->html->tag(
            $tag,
            $this->translator->translate(
                $this->summary,
                [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ],
                'yii-gridview',
            ),
            $summaryOptions
        );
    }
}
