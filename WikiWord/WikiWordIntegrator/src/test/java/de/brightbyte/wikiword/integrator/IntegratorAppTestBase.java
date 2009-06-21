package de.brightbyte.wikiword.integrator;

import java.io.IOException;
import java.net.URL;

import org.dbunit.operation.DatabaseOperation;

import de.brightbyte.db.testing.DatabaseTestBase;
import de.brightbyte.wikiword.DatasetIdentifier;
import de.brightbyte.wikiword.TweakSet;

public abstract class IntegratorAppTestBase<T extends AbstractIntegratorApp> extends DatabaseTestBase {
	
	public IntegratorAppTestBase(String name) {
		super(name);
	}
	
    protected DatabaseOperation getSetUpOperation() throws Exception
    {
        return DatabaseOperation.NONE;
    }

	public TweakSet loadTweakSet() throws IOException {
		URL url = requireAuxilliaryFileURL("test-tweaks.properties");
		TweakSet tweaks = new TweakSet();
		tweaks.loadTweaks(url);
		return tweaks;
	}

	public FeatureSetSourceDescriptor loadSourceDescriptor(String testName) throws IOException {
		URL url = requireAuxilliaryFileURL(getBaseName()+"-"+testName+".properties");
		FeatureSetSourceDescriptor descriptor = new FeatureSetSourceDescriptor();
		descriptor.loadTweaks(url);
		return descriptor;
	}

	protected abstract T createApp();
	
	protected T prepareApp(FeatureSetSourceDescriptor sourceDescriptor, String targetTable) throws IOException {
		TweakSet tweaks = loadTweakSet();
		T app = createApp();
		
		app.setKeepAlive(true);
		app.testInit(testDataSource, DatasetIdentifier.forName("TEST", "xx"), tweaks, sourceDescriptor, targetTable);
		return app;
	}

	protected void runApp(String testName) throws Exception {
		//get source description
		FeatureSetSourceDescriptor source = loadSourceDescriptor(testName);
		
		//run application
		T app = prepareApp(source, testName);
		app.testLaunch();

		 //compare query result to expected data from XML file.
		 assertTableData(getBaseName()+"-"+testName, app.getConfiguredDataset().getDbPrefix()+testName);
	}
	
}
