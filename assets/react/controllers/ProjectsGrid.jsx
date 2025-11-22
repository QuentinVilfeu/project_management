import React, { useState, useMemo, useEffect } from 'react';
import { AgGridReact } from 'ag-grid-react'; // React Data Grid Component

import 'ag-grid-community/styles/ag-theme-alpine.css';

export default function ProjectsGrid(props) {

	const [projects, setProjects] = useState(props.projects || []);

	useEffect(() => {
		fetchProjects();
	}, []);

	const fetchProjects = async () => {
		try {
			const response = await fetch('/fr/project/get/projects');
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			const data = await response.json();
			setProjects(data);
		} catch (error) {
			console.error('Error fetching projects:', error);
		}
	};

	const columnDefs = useMemo(() => [
		{ headerName: 'ID', field: 'id', sortable: true, filter: true, width: 90},
		{ headerName: 'Title', field: 'title', sortable: true, filter: true, flex: 1, filterParams: {
            maxNumConditions: 4
        }},
		{ headerName: 'Description', field: 'description', sortable: true, filter: true, flex: 2 },
	], []);

	return (
		<div className="ag-theme-alpine" style={{ height: '400px', width: '100%' }}>
			<AgGridReact
				columnDefs={columnDefs}
				rowData={projects}
				rowSelection="single"
				animateRows={true}
			/>
		</div>
	);
}
