package de.brightbyte.wikiword.integrator;

import java.io.IOException;

import de.brightbyte.data.cursor.DataCursor;
import de.brightbyte.util.PersistenceException;
import de.brightbyte.wikiword.integrator.data.Association;
import de.brightbyte.wikiword.integrator.data.FeatureMapping;
import de.brightbyte.wikiword.integrator.processor.ConceptAssociationPassThrough;
import de.brightbyte.wikiword.integrator.processor.ConceptAssociationProcessor;
import de.brightbyte.wikiword.integrator.store.ConceptAssociationStoreBuilder;
import de.brightbyte.wikiword.integrator.store.AssociationFeature2ConceptAssociationStoreBuilder;
import de.brightbyte.wikiword.integrator.store.AssociationFeatureStoreBuilder;
import de.brightbyte.wikiword.integrator.store.DatabaseConceptAssociationStoreBuilder;
import de.brightbyte.wikiword.integrator.store.DatabaseConceptMappingStoreBuilder;
import de.brightbyte.wikiword.integrator.store.DatabaseConceptMappingStoreBuilder.Factory;
import de.brightbyte.wikiword.store.WikiWordStoreFactory;

/**
 * This is the primary entry point to the first phase of a WikiWord analysis.
 * ImportDump can be invoked as a standalone program, use --help as a
 * command line parameter for usage information.
 */
public class BuildConceptAssociations extends AbstractIntegratorApp<AssociationFeatureStoreBuilder, ConceptAssociationProcessor, Association> {
	
	@Override
	protected WikiWordStoreFactory<? extends AssociationFeatureStoreBuilder> createConceptStoreFactory() throws IOException, PersistenceException {
		DatabaseConceptAssociationStoreBuilder.Factory mappingStoreFactory= new DatabaseConceptAssociationStoreBuilder.Factory(
				getTargetTableName(), 
				getConfiguredDataset(), 
				getConfiguredDataSource(), 
				tweaks);
		
		FeatureSetSourceDescriptor sourceDescriptor = getSourceDescriptor();
		
		FeatureMapping fm = new FeatureMapping();
		fm.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.FOREIGN_AUTHORITY, sourceDescriptor, "authority-name-field", "=" + sourceDescriptor.getAuthorityName(), String.class);
		fm.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.FOREIGN_ID, sourceDescriptor, "foreign-id-field", null, String.class);
		fm.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.FOREIGN_NAME, sourceDescriptor, "foreign-name-field", null, String.class);

		FeatureMapping  cm = new FeatureMapping();
		cm.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.CONCEPT_ID, sourceDescriptor, "concept-id-field", null, Integer.class);
		cm.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.CONCEPT_NAME, sourceDescriptor, "concept-name-field", null, String.class);

		FeatureMapping am = new FeatureMapping();
		am.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.CONCEPT_PROPERTY, sourceDescriptor, "concept-property-field", null, String.class);
		am.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.CONCEPT_PROPERTY_SOURCE, sourceDescriptor, "concept-property-source-field", null, String.class);
		am.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.CONCEPT_PROPERTY_FREQ, sourceDescriptor, "concept-property-freq-field", null, Integer.class);
		
		am.addMapping(AssociationFeature2ConceptAssociationStoreBuilder.ASSOCIATION_WEIGHT, sourceDescriptor, "association-weight-field", null, Double.class);
		
		return new AssociationFeature2ConceptAssociationStoreBuilder.Factory<DatabaseConceptAssociationStoreBuilder>(
				mappingStoreFactory,
				fm, cm, am
				);
	}

	@Override
	protected void run() throws Exception {
		AssociationFeatureStoreBuilder store = getStoreBuilder();
		this.propertyProcessor = createProcessor(store); 

		section("-- fetching properties --------------------------------------------------");
		DataCursor<Association> cursor = openAssociationCursor();
		
		section("-- process properties --------------------------------------------------");
		store.prepareImport();
		
		this.propertyProcessor.processAssociations(cursor);
		cursor.close();

		store.finalizeImport();
	}	

	@Override
	protected ConceptAssociationProcessor createProcessor(AssociationFeatureStoreBuilder conceptStore) throws InstantiationException {
		//		FIXME: parameter list is restrictive, pass descriptor 
		return instantiate(sourceDescriptor, "conceptAssociationProcessorClass", ConceptAssociationPassThrough.class, conceptStore);
	}
	
	public static void main(String[] argv) throws Exception {
		LoadForeignProperties app = new LoadForeignProperties();
		app.launch(argv);
	}
}