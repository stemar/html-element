<?php
require_once __DIR__.'/HTMLElement.php';

class HTMLElementTest extends PHPUnit\Framework\TestCase {

    function test_construct_class() {
        $html = $this->html_line();
        $html_element = new HTMLElement($html);
        $this->assertInstanceOf(DOMElement::class, $html_element);
    }

    function test_nodeValue_line() {
        $html = $this->html_line();
        $html_element = new HTMLElement($html);
        $actual = $html_element->elements('//p')[0]->nodeValue;
        $this->assertEquals('ØÜ Link', $actual);
        $actual = $html_element->elements('//a')[0]->nodeValue;
        $this->assertEquals('Link', $actual);
    }

    function test_static_nodeValue_line() {
        $html = $this->html_line();
        $actual = HTMLElement::new($html)->elements('//p')[1]->nodeValue;
        $this->assertEquals('Second para', $actual);
        $actual = HTMLElement::new($html)->xpath('//p');
        $expected = [
            '<p>ØÜ <a href="#">Link</a></p>',
            '<p>Second para</p>',
        ];
        $this->assertEquals($expected, $actual);

        $html = '<p>First paragraph <a href="#">Link</a></p><p>Second paragraph</p>';
        $actual = HTMLElement::new($html)->xpath('//p');
        $expected = [
            '<p>First paragraph <a href="#">Link</a></p>',
            '<p>Second para</p>',
        ];

        $html = $this->html_full();
        $actual = HTMLElement::new($html)->elements('//form/fieldset//legend')[1]->nodeValue;
        $expected = 'Radio buttons';
        $this->assertEquals($expected, $actual);
        $actual = HTMLElement::new($html)->xpath('//form/fieldset//legend')[1];
        $expected = <<<HTML
<legend class="mt-4">Radio buttons</legend>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_nodeValue_block() {
        $html = $this->html_block();
        $html_element = new HTMLElement($html);
        $element = $html_element->elements('//li')[2];
        $this->assertEquals('Three', $element->nodeValue);
        $this->assertEquals('li', $element->tagName);
        $this->assertEquals('bar a', $element->attributes->item(0)->nodeValue);
        $this->assertEquals('bar a', $element->getAttribute('class'));
        $this->assertEquals('bar a', $element->getAttributeNode('class')->nodeValue);
    }

    function test_xpath_input_non_blank_value() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->xpath('//form/fieldset//input[@value][not(@value="")]');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, $elements);
        $expected = <<<HTML
<input type="text" readonly class="form-control-plaintext" id="staticEmail" name="staticEmail" value="email@example.com">
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="option1" checked>
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios2" value="option2">
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_xpath_entities() {
        $html = $this->html_line();
        $html_element = new HTMLElement($html);
        $actual = $html_element->xpath('//p')[0]; // Get first <p>
        $expected = '<p>ØÜ <a href="#">Link</a></p>';
        $this->assertEquals($expected, $actual);
    }

    function test_outerHTML_first_fieldset() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $element = $html_element->elements('//form/fieldset//fieldset')[0];
        $this->assertIsNotArray($element);
        $actual = $html_element->outerHTML($element);
        $expected = <<<HTML
<fieldset class="form-group">
                    <legend class="mt-4">Radio buttons</legend>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Option one is this and that—be sure to include why it's great
                      </label>
                    </div>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios2" value="option2">
                        Option two can be something else and selecting it will deselect option one
                      </label>
                    </div>
                    <div class="form-check disabled">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
                        Option three is disabled
                      </label>
                    </div>
                  </fieldset>
HTML;
        $this->assertEquals($expected, $actual);
        return $actual;
    }

    function test_outerHTML_input() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//fieldset//input');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="option1" checked>
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios2" value="option2">
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" name="flexCheckDefault">
<input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" name="flexCheckChecked" checked>
<input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked>
<input type="range" class="form-range" id="customRange1">
<input type="range" class="form-range" id="disabledRange" disabled>
<input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3">
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_outerHTML_input_checked() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//fieldset//input[@checked]');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="option1" checked>
<input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" name="flexCheckChecked" checked>
<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_outerHTML_input_disabled() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//fieldset//input[@disabled]');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
<input type="range" class="form-range" id="disabledRange" disabled>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_outerHTML_select_option_selected() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//select//option[@selected]');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<option selected>3</option>
<option selected>2</option>
<option selected>5</option>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_outerHTML_select_multiple_option_selected() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//select[@multiple]//option[@selected]');
        $this->assertIsArray($elements);
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<option selected>2</option>
<option selected>5</option>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_innerHTML_select_multiple_option_selected() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $element = $html_element->elements('//form/fieldset//select[@multiple]')[0];
        $this->assertIsNotArray($element);
        $actual = $html_element->innerHTML($element);
        $expected = <<<HTML

                      <option>1</option>
                      <option selected>2</option>
                      <option>3</option>
                      <option>4</option>
                      <option selected>5</option>
                    
HTML;
        $this->assertEquals($expected, $actual);
        return $actual;
    }

    /**
     * @depends test_innerHTML_select_multiple_option_selected
     */
    function test_innerHTML_select_multiple_option_selected_outerHTML($html) {
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//option[@selected]');
        $actual = join(PHP_EOL, array_map([$html_element, 'outerHTML'], $elements));
        $expected = <<<HTML
<option selected>2</option>
<option selected>5</option>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_getAttribute_select() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//select');
        $this->assertIsArray($elements);
        $actual = [];
        foreach ($elements as $element) {
            $actual []= $element->getAttribute('name');
        }
        $expected = ['exampleSelect1', 'exampleSelect2'];
        $this->assertEquals($expected, $actual);
    }

    function test_nodeValue_select_multiple_option() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $elements = $html_element->elements('//form/fieldset//select[@multiple]//option');
        $this->assertIsArray($elements);
        $actual = array_map(function($element) {return $element->nodeValue;}, $elements);
        $expected = ['1', '2', '3', '4', '5'];
        $this->assertEquals($expected, $actual);
    }

    function test_getElementById_fieldset() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $element = $html_element->elements('//form/fieldset')[0];
        $this->assertIsNotArray($element);
        $e = $element->ownerDocument->getElementById('optionsRadios3');
        $actual = $html_element->outerHTML($e);
        $expected = <<<HTML
<input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function test_setAttribute_fieldset() {
        $html = $this->html_full();
        $html_element = new HTMLElement($html);
        $element = $html_element->elements('//form/fieldset//*[@id="optionsRadios3"]')[0];
        $this->assertIsNotArray($element);
        $e = $element->setAttribute('name', 'modifiedOptionsRadios');
        $actual = $html_element->outerHTML($element);
        $expected = <<<HTML
<input type="radio" class="form-check-input" name="modifiedOptionsRadios" id="optionsRadios3" value="option3" disabled>
HTML;
        $this->assertEquals($expected, $actual);
    }


    /**
     * @depends test_outerHTML_first_fieldset
     */
    function _test_tidy($html) {
//         $html = '<html>
//     <head>
//         <title></title>
//     </head>
//     <body>'.$html.'</body>
// </html>';
        $html_element = new HTMLElement($html);
        $actual = $html_element->tidy($html);
        // echo $actual;
        $expected = <<<HTML
<body>
    <fieldset class="form-group">
        <legend class="mt-4">Radio buttons</legend>
        <div class="form-check">
            <label class="form-check-label"><input type="radio"
            class="form-check-input" name="optionsRadios" id=
            "optionsRadios1" value="option1" checked> Option one is
            this and that—be sure to include why it's great</label>
        </div>
        <div class="form-check">
            <label class="form-check-label"><input type="radio"
            class="form-check-input" name="optionsRadios" id=
            "optionsRadios2" value="option2"> Option two can be
            something else and selecting it will deselect option
            one</label>
        </div>
        <div class="form-check disabled">
            <label class="form-check-label"><input type="radio"
            class="form-check-input" name="optionsRadios" id=
            "optionsRadios3" value="option3" disabled> Option three
            is disabled</label>
        </div>
    </fieldset>
</body>
HTML;
        $this->assertEquals($expected, $actual);
    }

    function html_line() {
        return '</z><p>Ø&Uuml; <a href="#">Link</a></p><p>Second para</p>';
    }

    function html_multiline() {
        return <<<HTML
</z>

<p>Ø&Uuml; 


<a href="#">Link</a>
</p><p>
Second para</p>
HTML;
    }

function html_block() {
        return <<<HTML
  <div id="article" class="block large">
    </z><!-- invalid node /z -->
    <h2>Article Name</h2>
    <p>Contents of article</p>
    <ul>
      <li class="a">One</li>
      <li class="bar">Two</li>
      <li class="bar a">Three</li>
      <li>Four</li>
      <li><a href="#">Five</a></li>
    </ul>
    <select name="dropdown">
      <option value="opt1">First option</option>
      <option value="opt2" selected>Second option</option>
    </select>
  </div>
HTML;
    }

    function html_full() {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bootswatch: Sandstone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../5/sandstone/bootstrap.css">
    <link rel="stylesheet" href="../_vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../_vendor/prismjs/themes/prism-okaidia.css">
    <link rel="stylesheet" href="../_assets/css/custom.min.css">
    <!-- Global Site Tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-23019901-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-23019901-1');
    </script>
  </head>
  <body>
    <div class="container">
      <div class="bs-docs-section">
        <div class="row">
          <div class="col-lg-12">
            <div class="page-header">
              <h1 id="forms">Forms</h1>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="bs-component">
              <form>
                <fieldset>
                  <legend>Legend</legend>
                  <div class="form-group row">
                    <label for="staticEmail" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                      <input type="text" readonly class="form-control-plaintext" id="staticEmail" name="staticEmail" value="email@example.com">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1" class="form-label mt-4">Email address</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" name="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1" class="form-label mt-4">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1" name="exampleInputPassword1" placeholder="Password">
                  </div>
                  <div class="form-group">
                    <label for="exampleSelect1" class="form-label mt-4">Example select</label>
                    <select class="form-select" id="exampleSelect1" name="exampleSelect1">
                      <option>1</option>
                      <option>2</option>
                      <option selected>3</option>
                      <option>4</option>
                      <option>5</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="exampleSelect2" class="form-label mt-4">Example multiple select</label>
                    <select multiple class="form-select" id="exampleSelect2" name="exampleSelect2">
                      <option>1</option>
                      <option selected>2</option>
                      <option>3</option>
                      <option>4</option>
                      <option selected>5</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="exampleTextarea" class="form-label mt-4">Example textarea</label>
                    <textarea class="form-control" id="exampleTextarea" name="exampleTextarea" rows="3"></textarea>
                  </div>
                  <div class="form-group">
                    <label for="formFile" class="form-label mt-4">Default file input example</label>
                    <input class="form-control" type="file" id="formFile" name="formFile">
                  </div>
                  <fieldset class="form-group">
                    <legend class="mt-4">Radio buttons</legend>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="option1" checked>
                        Option one is this and that&mdash;be sure to include why it's great
                      </label>
                    </div>
                    <div class="form-check">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios2" value="option2">
                        Option two can be something else and selecting it will deselect option one
                      </label>
                    </div>
                    <div class="form-check disabled">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios3" value="option3" disabled>
                        Option three is disabled
                      </label>
                    </div>
                  </fieldset>
                  <fieldset class="form-group">
                    <legend class="mt-4">Checkboxes</legend>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" name="flexCheckDefault">
                      <label class="form-check-label" for="flexCheckDefault">
                        Default checkbox
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" name="flexCheckChecked" checked>
                      <label class="form-check-label" for="flexCheckChecked">
                        Checked checkbox
                      </label>
                    </div>
                  </fieldset>
                  <fieldset>
                    <legend class="mt-4">Switches</legend>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
                      <label class="form-check-label" for="flexSwitchCheckDefault">Default switch checkbox input</label>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked>
                      <label class="form-check-label" for="flexSwitchCheckChecked">Checked switch checkbox input</label>
                    </div>
                  </fieldset>
                  <fieldset class="form-group">
                    <legend class="mt-4">Ranges</legend>
                      <label for="customRange1" class="form-label">Example range</label>
                      <input type="range" class="form-range" id="customRange1">
                      <label for="disabledRange" class="form-label">Disabled range</label>
                      <input type="range" class="form-range" id="disabledRange" disabled>
                      <label for="customRange3" class="form-label">Example range</label>
                      <input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3">
                  </fieldset>
                  <button type="submit" class="btn btn-primary">Submit</button>
                </fieldset>
              </form>
            </div>
          </div>
          <div class="col-lg-4 offset-lg-1">
            <form class="bs-component">
              <div class="form-group">
                <fieldset disabled>
                  <label class="form-label" for="disabledInput">Disabled input</label>
                  <input class="form-control" id="disabledInput" type="text" placeholder="Disabled input here..." disabled>
                </fieldset>
              </div>

              <div class="form-group">
                <fieldset>
                  <label class="form-label mt-4" for="readOnlyInput">Readonly input</label>
                  <input class="form-control" id="readOnlyInput" type="text" placeholder="Readonly input here..." readonly>
                </fieldset>
              </div>

              <div class="form-group has-success">
                <label class="form-label mt-4" for="inputValid">Valid input</label>
                <input type="text" value="correct value" class="form-control is-valid" id="inputValid">
                <div class="valid-feedback">Success! You've done it.</div>
              </div>

              <div class="form-group has-danger">
                <label class="form-label mt-4" for="inputInvalid">Invalid input</label>
                <input type="text" value="wrong value" class="form-control is-invalid" id="inputInvalid">
                <div class="invalid-feedback">Sorry, that username's taken. Try another?</div>
              </div>

              <div class="form-group">
                <label class="col-form-label col-form-label-lg mt-4" for="inputLarge">Large input</label>
                <input class="form-control form-control-lg" type="text" placeholder=".form-control-lg" id="inputLarge">
              </div>

              <div class="form-group">
                <label class="col-form-label mt-4" for="inputDefault">Default input</label>
                <input type="text" class="form-control" placeholder="Default input" id="inputDefault">
              </div>

              <div class="form-group">
                <label class="col-form-label col-form-label-sm mt-4" for="inputSmall">Small input</label>
                <input class="form-control form-control-sm" type="text" placeholder=".form-control-sm" id="inputSmall">
              </div>

              <div class="form-group">
                <label class="form-label mt-4">Input addons</label>
                <div class="form-group">
                  <div class="input-group mb-3">
                    <span class="input-group-text">$</span>
                    <input type="text" class="form-control" aria-label="Amount (to the nearest dollar)">
                    <span class="input-group-text">.00</span>
                  </div>
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="button-addon2">
                    <button class="btn btn-primary" type="button" id="button-addon2">Button</button>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label mt-4">Floating labels</label>
                <div class="form-floating mb-3">
                  <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                  <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating">
                  <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                  <label for="floatingPassword">Password</label>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>

    <script src="../_vendor/jquery/dist/jquery.min.js"></script>
    <script src="../_vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../_vendor/prismjs/prism.js" data-manual></script>
    <script src="../_assets/js/custom.js"></script>
  </body>
</html>
HTML;
    }
}
