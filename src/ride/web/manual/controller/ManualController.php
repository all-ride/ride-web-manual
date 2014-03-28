<?php

namespace ride\web\manual\controller;

use ride\library\http\Response;
use ride\library\manual\Manual;
use ride\library\manual\Page;
use ride\library\validation\exception\ValidationException;

use ride\web\base\controller\AbstractController;

/**
 * Controller for a manual
 */
class ManualController extends AbstractController {

    /**
     * Instance of the manual
     * @var ride\library\manual\Manual
     */
    protected $manual;

    /**
     * Constructs a manual controller
     * @param ride\library\manual\Manual $this->manual
     */
    public function __construct(Manual $manual) {
        $this->manual = $manual;
    }

    /**
     * Hook after every action
     * @return null
     */
    public function postAction() {
        $view = $this->response->getView();
        if (!$view) {
            return;
        }

        $template = $view->getTemplate();
        $template->set('pages', $this->getPages());
    }

    /**
     * Action to show the index of the manual
     * @param ride\library\manual\Manual $this->manual Instance of the manual
     * @return null
     */
    public function indexAction() {
        $this->setTemplateView('manual/index');
    }

    /**
     * Action to show the contents of a manual page
     * @return null
     */
    public function pageAction() {
        $this->getPageParameters(func_get_args(), $page, $path);

        // check for existance
        if (!$page || !$this->manual->hasPage(urlencode($page), $path)) {
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);

            // $this->formAction(trim($path, '/'), $page);

            return;
        }

        $decorator = $this->dependencyInjector->get('ride\\library\\decorator\\Decorator', 'markdown');
        $page = $this->manual->getPage(urlencode($page), $path);

        $title = $page->getTitle();
        $content = $page->getParsedContent($decorator, $this->request->getBaseScript());

        $this->setTemplateView('manual/page', array(
        	'title' => $title,
        	'content' => $content,
        ));
    }

    /**
     * Action to add or edit a page
     * @return null
     */
    public function formAction() {
        $this->gePageParameters(func_get_args(), $page, $path);

        if ($page && !$this->manual->hasPage($page, $path)) {
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);
        } else {
            $page = $this->manual->getPage($page, $path);
        }

        if (!$page instanceof Page) {
            $page = new Page($page, null, $path);

            $isNew = true;
        } else {
            $isNew = false;
        }

        $translator = $this->getTranslator();
        $referer = $this->request->getQueryParameter('referer', $this->getUrl('manual'));

        $formBuilder = $this->createFormBuilder($page);
        $formBuilder->addRow('title', 'string', array(
        	'label' => $translator->translate('label.title'),
            'validators' => array(
        	   'required' => array(),
            ),
        ));
        $formBuilder->addRow('content', 'text', array(
        	'label' => $translator->translate('label.content'),
            'attributes' => array(
                'rows' => 12,
            )
        ));
        $formBuilder->addRow('path', 'string', array(
        	'label' => $translator->translate('label.path'),
        ));
        $formBuilder->setRequest($this->request);

        $form = $formBuilder->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                $this->response->setRedirect($referer);

                return;
            }

            try {
                $form->validate();

                $page = $form->getData();

                $this->manual->savePage($page);

                $this->addSuccess('success.data.saved', array('data' => $page->getTitle()));

                $this->response->setRedirect($this->getUrl('manual.page') . $page->getRoute());

                return;
            } catch (ValidationException $exception) {
                $form->setValidationException($exception);
            }
        }

        if ($isNew) {
            $this->addWarning('warning.manual.page.not.exist');
        }

        $this->setTemplateView('manual/form', array(
        	'form' => $form->getView(),
            'isNew' => $isNew,
        	'referer' => $referer,
        ));
    }

    /**
     * Action to perform a manual search
     * @return null
     */
    public function searchAction() {
        if ($this->request->isPost()) {
            $query = $this->request->getBodyArgument('query');

            $this->response->setRedirect($this->request->getUrl() . '?query=' . urlencode($query));

            return;
        }

        $query = $this->request->getQueryParameter('query');

        $result = $this->manual->searchPages($query);
        foreach ($result as $page) {
            $page->setUrl($this->getUrl(self::ROUTE_PAGE) . $page->getRoute());
        }

        $this->setTemplateView('manual/search', array(
        	'query' => $query,
            'result' => $result,
        ));
    }

    /**
     * Action to show the reference
     * @return null
     */
    public function referenceAction() {
        $references = array(
            'Events' => $this->manual->getReference('events'),
            'Parameters' => $this->manual->getReference('parameters'),
        );

        $this->setTemplateView('manual/reference', array(
        	'references' => $references,
        ));
    }

    /**
     * Gets the path and page based on the function arguments
     * @param array $arguments Function arguments
     * @param string $page Name of the page
     * @param string $path Path of the page
     * @return null
     */
    protected function getPageParameters(array $arguments, &$page, &$path) {
        $path = '/';
        if ($arguments) {
            $page = array_pop($arguments);
            if ($arguments) {
                $path = '/' . implode('/', $arguments) . '/';
            }
        } else {
            $page = null;
        }
    }

    /**
     * Gets the pages to display in the sidebar
     * @param zibo\app\model\manual\Manual $manual Instance of the manual
     * @return array
     */
    protected function getPages() {
        $index = $this->manual->getIndex();

        ksort($index);

        $urlPage = $this->getUrl('manual.page');

        foreach ($index as $path => $pages) {
            ksort($index[$path]);

            foreach ($pages as $name => $page) {
                if ($page === true) {
                    $page = new Page(urldecode($name), '', $path);
                    $index[$path][$name] = $page;
                }

                if (!$page->getUrl()) {
                    $page->setUrl($urlPage . $page->getRoute());
                }
            }
        }

        $referencePage = new Page($this->getTranslator()->translate('title.reference'));
        $referencePage->setUrl($this->getUrl('manual.reference'));

        if (isset($index['/'])) {
            $index['/'] = array($referencePage) + $index['/'];
        } else {
            $index = array('/' => array($referencePage)) + $index;
        }

        return $index;
    }

}
