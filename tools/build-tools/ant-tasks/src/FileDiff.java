import java.io.*;
import java.util.Vector;

import org.apache.tools.ant.BuildException;
import org.apache.tools.ant.Task;
import org.apache.tools.ant.types.RegularExpression;


public class FileDiff extends Task {

	private String property;
	private File file1;
	private File file2;
	private Vector ignores = new Vector();

	public void setFile1 ( File file1 ) {
		this.file1 = file1;
	}

	public void setFile2 ( File file2 ) {
		this.file2 = file2;
	}

	public void setProperty ( String property ) {
		this.property = property;
	}
	
	public void addIgnore ( Ignore ignore ) {
		this.ignores.add( ignore );
	}

	public void execute () throws BuildException {
		if ( property == null || property.length() == 0 )
			throw new BuildException( "You need to specify a property that is set if the files match." );
		if ( file1 == null || file2 == null )
			throw new BuildException( "You need to specify two files." );
		try {
			if ( !file1.exists() && !file2.exists() )
				throw new BuildException( "The two files do not exist." );
			else if ( !file1.exists() ) {
				getProject().setProperty( property, "false" ); // files differ
				return;
			}
			else if ( !file2.exists() ) {
				getProject().setProperty( property, "false" ); // files differ
				return;
			}
			if ( !file1.canRead() )
				throw new BuildException( "File " + file1.getPath() + " is not readable." );
			if ( !file2.canRead() )
				throw new BuildException( "File " + file2.getPath() + " is not readable." );
			// check that <ignores/> are okay:
			for ( int i=0; i<ignores.size(); i++ ) {
				Ignore ignore = (Ignore)ignores.get( i );
				String prefix = ignore.getPrefix();
				if ( prefix == null || prefix.length() == 0 )
					throw new BuildException( "You specified an invalid or empty prefix in an ignore element." );
			}
			
			BufferedReader in1 = new BufferedReader( new FileReader( file1 ) );
			BufferedReader in2 = new BufferedReader( new FileReader( file2 ) );
			while ( in1.ready() && in2.ready() ) {
				String line1 = in1.readLine();
				String line2 = in2.readLine();
				boolean ignoreLine = false;
				
				// handle ignores:
				for ( int i=0; i<ignores.size(); i++ ) {
					Ignore ignore = (Ignore)ignores.get( i );
					String prefix = ignore.getPrefix();
					if ( line1.startsWith( prefix ) && line2.startsWith( prefix ) ) {
						ignoreLine = true;
						break;
					}
				}
				
				// compare lines:
				if ( !ignoreLine && !line1.equals( line2 ) )
					return; // files differ
			}
			if ( in1.ready() != in2.ready() )
				return; // files differ
			
			getProject().setProperty( property, "true" ); // files match
		}
		catch ( Exception e ) {
			throw new BuildException( e.toString() );
		}
	}
}
