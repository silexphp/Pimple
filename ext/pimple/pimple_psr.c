
/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2014 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

#include "php.h"

#include "pimple_psr.h"

PHPAPI zend_class_entry *pimple_ce_PsrContainerInterface;
PHPAPI zend_class_entry *pimple_ce_PsrContainerExceptionInterface;
PHPAPI zend_class_entry *pimple_ce_PsrNotFoundExceptionInterface;

ZEND_BEGIN_ARG_INFO_EX(arginfo_pimple_PsrContainerInterface_get, 0, 0, 1)
ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_pimple_PsrContainerInterface_has, 0, 0, 1)
ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

static const zend_function_entry pimple_ce_PsrContainerInterface_functions[] = {
	PHP_ABSTRACT_ME(ContainerInterface, get, arginfo_pimple_PsrContainerInterface_get)
	PHP_ABSTRACT_ME(ContainerInterface, has, arginfo_pimple_PsrContainerInterface_has)
	PHP_FE_END
};

static const zend_function_entry pimple_ce_PsrContainerExceptionInterface_functions[] = {
	PHP_FE_END
};

static const zend_function_entry pimple_ce_PsrNotFoundExceptionInterface_functions[] = {
	PHP_FE_END
};

PHP_MINIT_FUNCTION(pimple_psr)
{
	zend_class_entry tmp_ce_PsrContainerInterface;
	zend_class_entry tmp_ce_PsrContainerExceptionInterface;
	zend_class_entry tmp_ce_PsrNotFoundExceptionInterface;

	INIT_NS_CLASS_ENTRY(tmp_ce_PsrContainerInterface,          PSR_CONTAINER_NS, "ContainerInterface",          pimple_ce_PsrContainerInterface_functions);
	INIT_NS_CLASS_ENTRY(tmp_ce_PsrContainerExceptionInterface, PSR_CONTAINER_NS, "ContainerExceptionInterface", pimple_ce_PsrContainerExceptionInterface_functions);
	INIT_NS_CLASS_ENTRY(tmp_ce_PsrNotFoundExceptionInterface,  PSR_CONTAINER_NS, "NotFoundExceptionInterface",  pimple_ce_PsrNotFoundExceptionInterface_functions);

	pimple_ce_PsrContainerInterface          = zend_register_internal_interface(&tmp_ce_PsrContainerInterface TSRMLS_CC);
	pimple_ce_PsrContainerExceptionInterface = zend_register_internal_interface(&tmp_ce_PsrContainerExceptionInterface TSRMLS_CC);
	pimple_ce_PsrNotFoundExceptionInterface  = zend_register_internal_interface(&tmp_ce_PsrNotFoundExceptionInterface TSRMLS_CC);

	zend_class_implements(pimple_ce_PsrNotFoundExceptionInterface TSRMLS_CC, 1, pimple_ce_PsrContainerExceptionInterface);

	return SUCCESS;
}
