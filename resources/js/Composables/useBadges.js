/**
 * Badge utility composable for mapping status/severity values to badge colors
 */
export function useBadges() {
    /**
     * Get badge color for project status
     * @param {string} status - Project status value
     * @returns {string} Badge color prop
     */
    const getProjectStatusColor = (status) => {
        const statusMap = {
            'on_track': 'green',
            'completed': 'green',
            'Completed': 'green',
            'at_risk': 'amber',
            'Preparation': 'amber',
            'delayed': 'red',
            'Cancelled': 'red',
            'planning': 'blue',
            'in_progress': 'blue',
            'Execution': 'blue',
            'In Progress': 'blue',
            'Pending': 'gray',
        };
        
        return statusMap[status] || 'gray';
    };

    /**
     * Get badge color for risk level
     * @param {string} level - Risk level value
     * @returns {string} Badge color prop
     */
    const getRiskLevelColor = (level) => {
        const levelMap = {
            'critical': 'red',
            'Critical': 'red',
            'high': 'orange',
            'High': 'orange',
            'medium': 'amber',
            'Medium': 'amber',
            'low': 'green',
            'Low': 'green',
        };
        
        return levelMap[level] || 'gray';
    };

    /**
     * Get badge color for risk status
     * @param {string} status - Risk status value
     * @returns {string} Badge color prop
     */
    const getRiskStatusColor = (status) => {
        const statusMap = {
            'Open': 'red',
            'Mitigating': 'amber',
            'Closed': 'green',
        };
        
        return statusMap[status] || 'gray';
    };

    /**
     * Get badge color for RAG status
     * @param {string} rag - RAG status value
     * @returns {string} Badge color prop
     */
    const getRagColor = (rag) => {
        const ragLower = (rag || '').toLowerCase();
        const ragMap = {
            'red': 'red',
            'amber': 'amber',
            'green': 'green',
        };
        
        return ragMap[ragLower] || 'gray';
    };

    /**
     * Get badge color for incident severity
     * @param {string} severity - Incident severity value
     * @returns {string} Badge color prop
     */
    const getIncidentSeverityColor = (severity) => {
        // Same as risk level
        return getRiskLevelColor(severity);
    };

    /**
     * Get badge color for incident status
     * @param {string} status - Incident status value
     * @returns {string} Badge color prop
     */
    const getIncidentStatusColor = (status) => {
        const statusMap = {
            'reported': 'blue',
            'investigating': 'purple',
            'mitigating': 'amber',
            'resolved': 'green',
            'closed': 'green',
        };
        
        return statusMap[status] || 'gray';
    };

    /**
     * Get badge color for milestone status
     * @param {string} status - Milestone status value
     * @returns {string} Badge color prop
     */
    const getMilestoneStatusColor = (status) => {
        const statusMap = {
            'Completed': 'green',
            'In Progress': 'blue',
            'Pending': 'gray',
        };
        
        return statusMap[status] || 'gray';
    };

    /**
     * Get badge color for audit log action
     * @param {string} action - Audit log action
     * @returns {string} Badge color prop
     */
    const getAuditActionColor = (action) => {
        const actionMap = {
            'create': 'green',
            'update': 'blue',
            'delete': 'red',
            'login': 'purple',
        };
        
        return actionMap[action] || 'gray';
    };

    /**
     * Get badge color for data source
     * @param {string} source - Data source type
     * @returns {string} Badge color prop
     */
    const getDataSourceColor = (source) => {
        const sourceMap = {
            'manual': 'green',
            'Manual': 'green',
            'Live': 'green',
            'automated': 'amber',
            'simulation': 'amber',
            'Simulation': 'amber',
        };
        
        return sourceMap[source] || 'amber';
    };

    /**
     * Get badge color for alert severity
     * @param {string} severity - Alert severity
     * @returns {string} Badge color prop
     */
    const getAlertSeverityColor = (severity) => {
        const severityMap = {
            'critical': 'red',
            'warning': 'amber',
            'info': 'blue',
        };
        
        return severityMap[severity] || 'gray';
    };

    /**
     * Get badge color for risk score
     * @param {number} score - Risk score value
     * @returns {string} Badge color prop
     */
    const getRiskScoreColor = (score) => {
        if (score >= 20) return 'red';
        if (score >= 12) return 'orange';
        if (score >= 6) return 'amber';
        return 'green';
    };

    return {
        getProjectStatusColor,
        getRiskLevelColor,
        getRiskStatusColor,
        getRagColor,
        getIncidentSeverityColor,
        getIncidentStatusColor,
        getMilestoneStatusColor,
        getAuditActionColor,
        getDataSourceColor,
        getAlertSeverityColor,
        getRiskScoreColor,
    };
}
