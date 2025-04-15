function calculateCurrentYearLeave(endProbationDate) {
    const currentDate = new Date();
    const startYear = new Date(currentDate.getFullYear(), 0, 1);
    const monthDiff = Math.max(
        Math.floor(
            (currentDate - Math.max(new Date(endProbationDate), startYear)) / 
            (1000 * 60 * 60 * 24 * 30.44)
        ) + 1,
        0
    );
    
    const yearsFromProbation = Math.floor(
        (currentDate - new Date(endProbationDate)) / 
        (1000 * 60 * 60 * 24 * 365)
    );
    
    const bonusDays = currentDate.getMonth() === 11 && Math.floor(yearsFromProbation / 5) > 0 
        ? Math.floor(yearsFromProbation / 5) 
        : 0;
    
    return monthDiff + bonusDays;
}

function calculatePreviousYearLeave(endProbationDate) {
    const currentYear = new Date().getFullYear();
    const endYear = new Date(currentYear - 1, 11, 31);
    const yearsFromProbation = Math.floor(
        (endYear - new Date(endProbationDate)) / 
        (1000 * 60 * 60 * 24 * 365)
    );
    
    if (new Date(endProbationDate).getFullYear() <= currentYear - 2) {
        return 12 + Math.floor(yearsFromProbation / 5);
    }
    
    const monthDiff = Math.min(
        12,
        Math.max(
            0,
            Math.floor(
                (endYear - new Date(endProbationDate)) / 
                (1000 * 60 * 60 * 24 * 30.44)
            )
        )
    );
    
    return monthDiff + Math.floor(yearsFromProbation / 5);
}

function calculateRemainingLeave(currentYearLeave, previousYearLeave, usedLeave) {
    return currentYearLeave + previousYearLeave - usedLeave;
}