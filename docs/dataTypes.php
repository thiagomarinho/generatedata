<?php
$page = "dataTypes";
require_once("templates/header.php");
?>

<div class="container">
	<div class="row">

		<div class="span3 bs-docs-sidebar" id="pagenav">
			<ul class="nav nav-list bs-docs-sidenav" data-spy="affix">
				<li class="active"><a href="#overview"><i class="icon-chevron-right"></i> Overview</a></li>
				<li><a href="#anatomy"><i class="icon-chevron-right"></i> Anatomy of a Data Type</a></li>
				<li><a href="#filesAndFolders"><i class="icon-chevron-right"></i> &#8212; Files and Folders</a></li>
				<li><a href="#js"><i class="icon-chevron-right"></i> &#8212; JavaScript</a></li>
				<li><a href="#php"><i class="icon-chevron-right"></i> &#8212; PHP</a></li>
				<li><a href="#languageFiles"><i class="icon-chevron-right"></i> &#8212; Language Files</a></li>
				<li><a href="#phpClass"><i class="icon-chevron-right"></i> The PHP Class</a></li>
				<li><a href="#phpClassExample"><i class="icon-chevron-right"></i> &#8212; Example: GUID Data Type</a></li>
				<li><a href="#phpClassVars"><i class="icon-chevron-right"></i> &#8212; Class Vars</a></li>
				<li><a href="#phpClassMethods"><i class="icon-chevron-right"></i> &#8212; Class Methods</a></li>
				<li><a href="#jsModule"><i class="icon-chevron-right"></i> The JS Module</a></li>
				<li><a href="#availableResources"><i class="icon-chevron-right"></i> Available JS Resources</a></li>
				<li><a href="#updatingUI"><i class="icon-chevron-right"></i>Adding your Data Type</a></li>
				<li><a href="#contribute"><i class="icon-chevron-right"></i> How to Contribute</a></li>
			</ul>
		</div>
		<div class="span9">

			<a id="overview"></a>
			<section>
				<div class="page-header">
					<h1>Data Types</h1>
				</div>
				<p class="lead">
					Provide new types of data for generation.
				</p>
			</section>
 
			<section id="overview">
				<h2>Overview</h2>
				<p>
					This document explains how to add your own data types so you can generate pretty much whatever you want.
				</p>
				<p>
					Data Types are <b>self-contained plugins</b> that generate a single random data item, like a name, email address, country name,
					country code, image, picture, URL, barcode image, binary string - really anything you want. Data Types can offer basic 
					functionality, like the <code>Email Address</code> Data Type which has no options, examples or help doc, or they can 
					be more advanced, like the <code>Date</code> Data Type, which contains examples of date formats for easy generation, 
					and contains a date picker dialog (jQuery UI). Data Types can be standalone and generate data that has no bearing on 
					other fields - like the <code>Alpha Numeric</code> Data Type - or make decisions about its content based on other fields 
					in the data set, like <code>Region</code>, which intelligently generates a region within whatever country has been randomly 
					generated for that row. Finally, if you want to get <i>really</i> fancy, you can even create Data Types that 
					generate content based on previously generated <i>row</i> data, like the <code>Tree</code> Data Type that 
					creates a tree-like data structure by mapping the ID of each row to a single parent row ID.
				</p>
				<p>
					<b>Data Types have both a PHP and JS component</b>. The PHP is used to do the actual generation; the JS is used for 
					creating the UI and saving/loading the Data Type data.
				</p>
				<p>
					When creating your new Data Type, you can add anything you need from client-side validation to custom dynamic JS/DOM 
					manipulation. You can also generate different content based on the selected Export Type (SQL, XML etc). It's a pretty 
					flexible system, so hopefully you won't run into any brick walls. And if you do, you can just <a href="contribute.php">drop 
					me a line</a> and explain the shortcomings. 
				</p>
				<p>
					Lastly, I tried to make the process of adding Data Types as simple and as sandboxed as possible. The Core script does 
					an awful lot for you: all you really need to do is follow the instructions below and maybe look at the existing Data Types 
					for inspiration. Once you wrap your head about how it all fits together, developing new Data Types should be pretty
					straightforward.
				</p>
				<p>
					Alrighty! Let's start with looking at the actual files and folders that go into a Data Type.
				</p>
			</section>

			<hr />

			<section id="anatomy">
				<h2>Anatomy of a Data Type</h2>
				<p>
					Now let's do a high-level view of what goes into a module: the files and folders, the JS + PHP components
					and how the translations / internationalization works. We'll get into the details about the code in the following
					sections.
				</p>
			</section>

			<section id="filesAndFolders">
				<h3>Files and Folders</h3>
				<p>
					All Data Types are found in the <code>/resources/plugins/dataTypes/</code> folder. Each Data Type has its own folder, 
					which acts as the namespace for the JS and PHP code. What I mean is that the exact string you choose for the folder (like 
					<code>AlphaNumeric</code> or <code>StreetAddress</code>) <i>has to be</i> used in your JS module creation and PHP
					class definition. I'll explain all that below.
				</p>
				<p>
					A Data Type has the following <b>required</b> files. Let's assume the folder name is <code>MyNewDataType</code>.
				</p>
				<ul>
					<li><code>/resources/plugins/dataType/MyNewDataType.js</code>: this file can actually be called whatever you want, but for 
						consistency and for keeping reading the Web Inspector / Firebug net panel, I'd name them like this. You can have 
						as many JS files as you want, but one is almost certainly enough.</li>
					<li><code>/resources/plugins/dataType/MyNewDataType.class.php</code>: this contains your <code>DataType_MyNewDataType</code>
						class, which handles all necessary server-side code: the data generation and any markup you want available in the 
						generator webpage. More info about all that below.</li>
					<li><code>/resources/plugins/dataType/lang/en.php</code>: A PHP file containing a single array (hash) that lists all 
						strings used in your module.</li>
				</ul>
				<p>
					You can also include any custom CSS files you want. See the PHP class definition below for more information
				</p>
			</section>

			<section id="js">
				<h3>JavaScript</h3>

				<p>
					The JS module for your Data Type does the following:
				</p>

				<ul>
					<li>Registers itself with the <code>Manager</code> JS component, to allow it to publish and subscribe to messages; i.e.
						to interact with the Core script and detect when certain user interface events happen.</li>
					<li>Save and load data for each row that has your Data Type selected.</li>
					<li>Perform whatever validation is required to ensure the user fills in the Data Type row properly.</li>
					<li>Perform any additional UI frills, like hiding/showing/disabling/enabling content based on information entered 
						by the user in the page.</li>
				</ul>
			</section>

			<section id="php">
				<h3>PHP</h3>
				<p>
					The PHP class for your Data Type handles the following functionality:
				</p>
				<ul>
					<li>Initial installation of the module, if it needs to do anything special.</li>
					<li>Specifies in which section and what order in the Data Types dropdown your Data Type should appear.</li>
					<li>Specifies what JS and CSS files should be included for the Data Type when the generator is loaded.</li>
					<li>Creates whatever HTML is needed for the <code>Example</code> and <code>Options</code> columns in the generator table.</li>
					<li>Creates whatever HTML should be included in the Help section of the dialog window.</li>
					<li>Actually generate the random data for that Data Type.</li>
					<li>Specifies the process order of the Data Type. When the random data is generated, it's generated row by row. Within each
						row, each Data Type is generated in waves. The first wave are fields that have no dependencies with other row types; 
						the second and later waves may all depend on previous waves. That way, a Data Type that needs to know if another field 
						has a particular value can be sure that that value is actually loaded, and use that information in generating 
						the random snippet for that column and row. For example, a <code>Region</code> field can check to see if a
						<code>Country</code> field has been included, and if so, generate a random region within the country for that row.</li>
				</ul>
			</section>

			<section id="languageFiles">
				<h3>Language Files</h3>
				<p>
					All text strings that appear in your module should be pulled from a language file. It's very simple. Just create a 
					file called <code>en.php</code> in your <code>/resources/plugins/dataTypes/[data type folder]/lang/</code> folder.
					That file should contain a single <code>$L</code> hash, like so:
				</p>
	
<pre class="prettyprint linenums">
&lt;?php 

$L = array();

$L["name"] = "Alphanumeric";
$L["example_CanPostalCode"] = "(Can. Postal code)";
$L["example_Password"] = "(Password)";

// ...
</pre>

				<p>
					Once you do that, the Data Generator automatically makes that information accessible to your PHP and JS 
					code. I'll explain how that works in the following sections.
				</p>
			</section>

			<hr />

			<section id="phpClass">
				<h2>The PHP Class</h2>
				<p>
					All plugins - <code>Data Types</code>, <code>Export Types</code> and <code>Country</code> plugins have to extend 
					a base, abstract class defined by the core code. Hopefully you know what this means, but if not - time for some 
					Googling! Simply put, abstract classes are a mechanism to help ensure that the class being defined has a proper 
					footprint and contains all the functionality that's expected and required.
				</p>

				<p>
					For Data Types, take a look at this file: <code>/resources/classes/DataTypePlugin.class.php</code>. That's the 
					class you'll need to extend.
				</p>
			</section>

			<section id="phpClassExample">
				<h3>Example: GUID Data Type</h3>
				<p>
					Now rather than blather on about your Data Type PHP class in the abstract, let's look at an actual implementation
					first. If you want to see the complete list of available variables and methods, check out the source code 
					of the Data Type abstract class (<code>/resources/classes/DataTypePlugin.abstract.class.php</code>). It's well 
					documented.
				</p>
				<p>
					This is the PHP class for the <code>GUID</code> class. It's a simple Data Type that generates a random GUID string.
					Maybe first try it out in the script to see what it does.
				</p>

<pre class="prettyprint linenums">
&lt;?php

/**
 * @package DataTypes
 */

class DataType_GUID extends DataTypePlugin {
	protected $isEnabled = true;
	protected $dataTypeName = "GUID";
	protected $dataTypeFieldGroup = "numeric";
	protected $dataTypeFieldGroupOrder = 50;
	private $generatedGUIDs = array();

	public function generate($generator, $generationContextData) {
		$placeholderStr = "HHHHHHHH-HHHH-HHHH-HHHH-HHHH-HHHHHHHH";
		$guid = Utils::generateRandomAlphanumericStr($placeholderStr);

		// pretty sodding unlikely, but just in case!
		while (in_array($guid, $this->generatedGUIDs)) {
			$guid = Utils::generateRandomAlphanumericStr($placeholderStr);
		}
		$this->generatedGUIDs[] = $guid;
		return array(
			"display" => $guid
		);
	}

	public function getHelpHTML() {
		return "&lt;p&gt;{$this->L["help"]}&lt;/p&gt;";
	}

	public function getDataTypeMetadata() {
		return array(
			"SQLField" => "varchar(36) NOT NULL",
			"SQLField_Oracle" => "varchar2(36) NOT NULL"
		);
	}
}
</pre>

				<p>
					Let's look at each line in turn.
				</p>

				<ul>
					<li><code>class DataType_GUID extends DataTypePlugin</code>: our class definition. All Data Type class names
						must for of the following format: <code>DataType_[folder]</code> - where <i>folder</i> is the name
						of the Data Type folder. Pretty straightforward. Also, note that it extends the DataTypePlugin base class.
						That's required.</li>
					<li><code>$isEnabled</code>: this isn't strictly necessary, but good to include. It explictly enables the module. 
						In case you're tinkering around with a new Data Type, sometimes you may not want it to show up in the UI: in which 
						case you'd just set this to <code>false</code>.</li>
					<li><code>dataTypeName</code>: this is the human-readable name of your module. It can be in whatever
						language you want, but we prefer English as the default language string. The value you enter in this variable
						is <i>automatically overridden</i> if the current selected language has the following value in the language file:
						<code>$L["DATA_TYPE_NAME"] = "New Name";</code> This provides a simple mechanism to provide alternative translations
						of your Data Type names.</li>
					<li><code>$dataTypeFieldGroup</code>: in the Data Type dropdowns in the generator, you'll notice that the Data 
						Types are all grouped. This variable determines which group your Data Type should appear in. You can choose any of the 
						following strings: <code>human_data</code>, <code>geo</code>, <code>text</code>, <code>numeric</code>, <code>math</code>, 
						<code>other</code>. If you feel that you need a new group for your Data Type, <a href="contribute.php">drop me a line</a>.
					</li>
					<li><code>$dataTypeFieldGroupOrder</code>: this determines where in the list your Data Type should appear. Look at the 
						the values for other Data Types to figure out what value to enter. I spaced them all out with 10 in between to allow you 
						to insert your Data Type at any point in the list.</li>
				</ul>	

				<p>
					So far so good. The next line, <code>$generatedGUIDs</code> is a custom private var for use by this Data Type only. Don't worry 
					about it.
				</p>

				<p>
					Now lets look at the methods:
				</p>

				<ul>
					<li><code>public function generate($generator, $generationContextData)</code>: this is the main generation
						function for the Data Type. It's passed two parameters:
						<ol>
							<li><b>The current Generator instance</b>. Behinds the scenes, the data generation is all managed by the 
								<code>Generator</code> class, found here: <code>/resources/classes/Generator.class.php</code>. This is a very helpful class - 
								it contains various utility methods for finding out about the current data set being generated. However, the 
								<code>GUID</code> class doesn't need it.</li>
							<li><b>The generation context data</b>. The <code>Generator</code> generates the data sets row by row. Each row contains 
								one or more Data Types. This variable contains all the Data Types generated so far for the current 
								row. Any Data Type can choose to return additional meta data for a particular generated atomic data - e.g. a 
								Region could choose to return the <i>Country</i> to which is belongs. This second function param contains all that
								information. Lastly, if a Data Type has dependencies on previous Data Types in the row, it needs to 
								set the <code>protected $processOrder = X;</code> class variable. See the Data Type plugin abstract class
								for more information about that advanced feature - or look at the <code>Region</code> plugin for an example
								of how it's used.
							</li>
						</ol>
					</li>
					<li>public function getHelpHTML() {</li>
				</ul>

			</section>

			<hr />

			<section id="jsModule">
				<h2>The JS Module</h2>
				<p>
				</p>
			</section>

			<section id="availableResources">
				<h2>Available Resources</h2>
				<p>
				</p>
			</section>

			<section id="updatingUI">
				<h2>Adding your Data Type</h2>
				<p>
					When you add a new Data Type, just creating the new files and folders won't get it to show up in the UI. First,
					you'll need to follow the steps below to make sure your PHP class and JS Module has been created properly, and afterwards
					you'll need to refresh the UI.
				</p>
				<p>
					To update the list of available Data Types in the UI, go to the second <code>Settings</code> tab. There, click the 
					<code>Reset Plugins</code> button. A dialog will appears which resets all the available plugins (don't worry, this 
					won't cause any problems with saved content or anything like that). After refreshing the page, you should see
					your Data Type appear in the Data Type dropdowns in the generator.
				</p>
			</section>

			<section id="contribute">
				<h2>How to Contribute</h2>

				<p>
					If you feel that your Data Type could be of use to other people, send it our way! I'd love to take a look at it,
					and maybe even include it in the core script for others to download. Read the <a href="contribute.php">How to Contribute</a>.
				</p>
			</section>

		</div>
	</div>
</div>

<?php
require_once("templates/footer.php");
?>